import { ref } from 'vue'
import { supabase } from '@/lib/supabase'

let sharedChannel = null
let sharedMyId = null
let iceSendBuffer = []

export function useChatCall() {
  const callState = ref('idle')
  const callType = ref('audio')
  const callTimer = ref(0)
  const incomingCall = ref(null)
  const remoteUser = ref(null)

  let peerConnection = null
  let localStream = null
  let timerHandle = null
  let currentUserId = null
  let remoteUserId = null
  let callTimeout = null

  const ICE_SERVERS = {
    iceServers: [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' },
      { urls: 'stun:stun2.l.google.com:19302' }
    ]
  }

  const safeClose = (fn) => { try { fn() } catch {} }

  const cleanupCall = () => {
    if (timerHandle) { clearInterval(timerHandle); timerHandle = null }
    if (callTimeout) { clearTimeout(callTimeout); callTimeout = null }
    if (peerConnection) { safeClose(() => peerConnection.close()); peerConnection = null }
    if (localStream) { localStream.getTracks().forEach(t => t.stop()); localStream = null }
    callState.value = 'idle'
    callTimer.value = 0
    remoteUser.value = null
    remoteUserId = null
    iceSendBuffer = []
  }

  const startTimer = () => {
    callTimer.value = 0
    timerHandle = setInterval(() => { callTimer.value++ }, 1000)
  }

  const formatTime = (s) => {
    const m = Math.floor(s / 60)
    const sec = s % 60
    return `${m.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`
  }

  const ensureChannel = (myId) => {
    if (sharedChannel && sharedMyId === myId) return sharedChannel
    if (sharedChannel) { safeClose(() => supabase.removeChannel(sharedChannel)) }
    sharedMyId = myId

    sharedChannel = supabase.channel(`callsignal-${myId}`)

    sharedChannel.on('broadcast', { event: 'offer' }, async ({ payload }) => {
      if (payload.from === myId) return
      if (callState.value !== 'idle') {
        await sendTo(payload.from, 'busy', { from: myId })
        return
      }
      incomingCall.value = payload
      remoteUser.value = { id: payload.from, name: payload.fromName, photo: payload.fromPhoto }
      callType.value = payload.callType || 'audio'
      callState.value = 'ringing'
    })

    sharedChannel.on('broadcast', { event: 'answer' }, async ({ payload }) => {
      if (payload.from !== remoteUserId) return
      if (!peerConnection) return
      try {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(payload.answer))
        callState.value = 'connected'
        startTimer()
      } catch (e) { console.warn('Answer error:', e) }
    })

    sharedChannel.on('broadcast', { event: 'ice-candidate' }, async ({ payload }) => {
      if (!peerConnection) return
      try { await peerConnection.addIceCandidate(new RTCIceCandidate(payload.candidate)) } catch {}
    })

    sharedChannel.on('broadcast', { event: 'end-call' }, ({ payload }) => {
      if (payload.from === remoteUserId) cleanupCall()
    })

    sharedChannel.on('broadcast', { event: 'busy' }, () => {
      if (callState.value === 'calling') {
        cleanupCall()
      }
    })

    sharedChannel.subscribe()
    return sharedChannel
  }

  const sendTo = async (userId, event, payload) => {
    if (!sharedChannel) return
    const ch = supabase.channel(`callsignal-${userId}`)
    await ch.send({ type: 'broadcast', event, payload })
    safeClose(() => supabase.removeChannel(ch))
  }

  const getMedia = async (type) => {
    localStream = await navigator.mediaDevices.getUserMedia({
      audio: true,
      video: type === 'video' ? { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' } : false
    })
    return localStream
  }

  const createPeer = (myId, onRemoteStream) => {
    peerConnection = new RTCPeerConnection(ICE_SERVERS)

    peerConnection.onicecandidate = async (e) => {
      if (e.candidate && remoteUserId) {
        await sendTo(remoteUserId, 'ice-candidate', { candidate: e.candidate.toJSON(), from: myId })
      }
    }

    peerConnection.onconnectionstatechange = () => {
      const s = peerConnection?.connectionState
      if (s === 'failed' || s === 'closed') cleanupCall()
      if (s === 'disconnected') {
        callTimeout = setTimeout(() => { if (callState.value === 'connected') cleanupCall() }, 10000)
      }
      if (s === 'connected' && callTimeout) { clearTimeout(callTimeout); callTimeout = null }
    }

    if (onRemoteStream) {
      peerConnection.ontrack = (e) => {
        if (e.streams && e.streams[0]) {
          onRemoteStream(e.streams[0])
        }
      }
    }

    return peerConnection
  }

  const initListener = (myId) => {
    ensureChannel(myId)
  }

  const startCall = async (userId, userName, userPhoto, type, myId, myName, myPhoto) => {
    try {
      currentUserId = myId
      remoteUserId = userId
      remoteUser.value = { id: userId, name: userName, photo: userPhoto }
      callType.value = type
      callState.value = 'calling'

      ensureChannel(myId)

      await getMedia(type)
      createPeer(myId)
      localStream.getTracks().forEach(t => peerConnection.addTrack(t, localStream))

      const offer = await peerConnection.createOffer()
      await peerConnection.setLocalDescription(offer)

      await sendTo(userId, 'offer', {
        offer: offer.toJSON(),
        from: myId,
        fromName: myName,
        fromPhoto: myPhoto,
        callType: type
      })

      callTimeout = setTimeout(() => {
        if (callState.value === 'calling') cleanupCall()
      }, 30000)

      return { success: true }
    } catch (e) {
      cleanupCall()
      return { success: false, error: e.message }
    }
  }

  const acceptCall = async (offerPayload, myId, onRemoteStream) => {
    try {
      currentUserId = myId
      remoteUserId = offerPayload.from
      remoteUser.value = { id: offerPayload.from, name: offerPayload.fromName, photo: offerPayload.fromPhoto }
      callType.value = offerPayload.callType || 'audio'
      callState.value = 'connecting'

      ensureChannel(myId)

      await getMedia(callType.value)
      createPeer(myId, onRemoteStream)
      localStream.getTracks().forEach(t => peerConnection.addTrack(t, localStream))

      await peerConnection.setRemoteDescription(new RTCSessionDescription(offerPayload.offer))
      const answer = await peerConnection.createAnswer()
      await peerConnection.setLocalDescription(answer)

      await sendTo(offerPayload.from, 'answer', { answer: answer.toJSON(), from: myId })

      callState.value = 'connected'
      startTimer()
      incomingCall.value = null

      return { success: true }
    } catch (e) {
      cleanupCall()
      return { success: false, error: e.message }
    }
  }

  const endCall = async () => {
    if (remoteUserId && currentUserId) {
      await sendTo(remoteUserId, 'end-call', { from: currentUserId })
    }
    cleanupCall()
  }

  const declineCall = () => {
    if (remoteUserId && currentUserId) {
      sendTo(remoteUserId, 'busy', { from: currentUserId })
    }
    incomingCall.value = null
    callState.value = 'idle'
    remoteUser.value = null
  }

  const toggleMute = () => {
    if (localStream) {
      const t = localStream.getAudioTracks()[0]
      if (t) { t.enabled = !t.enabled; return t.enabled }
    }
    return false
  }

  const toggleVideo = () => {
    if (localStream) {
      const t = localStream.getVideoTracks()[0]
      if (t) { t.enabled = !t.enabled; return t.enabled }
    }
    return false
  }

  return {
    callState, callType, callTimer, incomingCall, remoteUser,
    initListener, startCall, acceptCall, endCall, declineCall,
    toggleMute, toggleVideo, formatTime, cleanupCall
  }
}
