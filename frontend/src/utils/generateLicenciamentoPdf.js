let pdfMakeInstance = null

async function getPdfMake() {
  if (!pdfMakeInstance) {
    const pdfMake = (await import('pdfmake/build/pdfmake')).default
    const pdfFonts = await import('pdfmake/build/vfs_fonts')
    pdfMake.vfs = pdfFonts.pdfMake ? pdfFonts.pdfMake.vfs : pdfFonts.vfs
    pdfMakeInstance = pdfMake
  }
  return pdfMakeInstance
}

const logoToBase64 = async (url) => {
  try {
    const resp = await fetch(url)
    const blob = await resp.blob()
    return new Promise((resolve) => {
      const reader = new FileReader()
      reader.onloadend = () => resolve(reader.result)
      reader.readAsDataURL(blob)
    })
  } catch {
    return null
  }
}

const estadoLabel = (s) => ({
  pendente: 'Pendente',
  documentacao_recebida: 'Documentação Recebida',
  submetido: 'Submetido',
  em_analise: 'Em Análise',
  aprovado: 'Aprovado',
  indeferido: 'Indeferido',
  resubmetido: 'Re-Submetido',
  certificacao_solicitada: 'Certificação Solicitada'
}[s] || s || '—')

const estadoColor = (s) => ({
  pendente: '#f59e0b',
  documentacao_recebida: '#3b82f6',
  submetido: '#8b5cf6',
  em_analise: '#f97316',
  aprovado: '#22c55e',
  indeferido: '#ef4444',
  resubmetido: '#6366f1',
  certificacao_solicitada: '#06b6d4'
}[s] || '#64748b')

const tipoLabel = (t) => ({
  importacao: 'Importação',
  exportacao: 'Exportação',
  trânsito: 'Trânsito',
  tranzito: 'Trânsito',
  licenca_especial: 'Licença Especial',
  outro: 'Outro'
}[t] || t || '—')

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatDateTime = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('pt-PT', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const parseObsUser = (text) => {
  if (!text) return ''
  const m = text.match(/\[([^\]]+)\]/)
  return m ? m[1] : ''
}

const parseObsDate = (text) => {
  if (!text) return ''
  const m = text.match(/^(\d{4}-\d{2}-\d{2})/)
  return m ? m[1] : ''
}

const parseObsText = (text) => {
  if (!text) return ''
  return text.replace(/^\d{4}-\d{2}-\d{2}\s*/, '').replace(/\[[^\]]+\]\s*$/, '').trim()
}

const divider = (color = '#1a365d') => ({
  canvas: [{ type: 'line', x1: 0, y1: 0, x2: 515, y2: 0, lineWidth: 0.5, lineColor: color }],
  margin: [0, 8, 0, 8]
})

const sectionBar = (title) => ({
  columns: [
    {
      width: 4,
      canvas: [{ type: 'rect', x: 0, y: 0, w: 4, h: 16, color: '#1a365d' }],
      margin: [0, 0, 0, 0]
    },
    {
      width: '*',
      text: title,
      style: 'sectionTitle',
      margin: [8, 0, 0, 0]
    }
  ],
  margin: [0, 12, 0, 6]
})

function infoRow(label, value, opts = {}) {
  return [
    { text: label, style: 'fieldLabel', width: '38%' },
    { text: value || '—', style: opts.bold ? 'fieldValueBold' : 'fieldValue', width: '62%' }
  ]
}

export async function generateLicenciamentoPdf(item, historico = [], estadosHistorico = []) {
  const logoBase64 = await logoToBase64('/assets/img/logo.png')
  const estado = item.estado || 'pendente'
  const cor = estadoColor(estado)
  const content = []

  // ── HEADER ──
  const headerCols = []
  if (logoBase64) {
    headerCols.push({
      width: 80,
      image: logoBase64,
      width: 70,
      margin: [0, 0, 0, 0]
    })
  }
  headerCols.push({
    width: '*',
    stack: [
      { text: 'FMLider', style: 'logoText' },
      { text: 'Transitário & Logística', style: 'logoSub' },
      { text: 'Estrada da Pedreira, Bairro da Vidrul – Cacuaco, Luanda', style: 'headerAddress' },
      { text: 'Tel: +244 935 141 747 | geral@fmlider.co.ao', style: 'headerAddress' }
    ],
    margin: [12, 0, 0, 0]
  })

  content.push({
    columns: headerCols,
    margin: [0, 0, 0, 6]
  })

  content.push(divider('#1a365d'))

  // ── TÍTULO + ESTADO ──
  content.push({
    columns: [
      {
        width: '*',
        stack: [
          { text: 'RELATÓRIO DE LICENCIAMENTO', style: 'pageTitle' },
          { text: `Referência: ${item.referencia || '—'}`, style: 'refCode' }
        ]
      },
      {
        width: 'auto',
        stack: [
          {
            text: estadoLabel(estado).toUpperCase(),
            style: 'statusBadge',
            color: '#ffffff'
          }
        ],
        margin: [0, 4, 0, 0]
      }
    ],
    margin: [0, 8, 0, 12]
  })

  // ── INFORMAÇÕES DO LICENCIAMENTO ──
  content.push(sectionBar('Informações do Licenciamento'))

  const infoBody = [
    [
      { text: 'Campo', style: 'tableHeader' },
      { text: 'Detalhe', style: 'tableHeader' },
      { text: 'Campo', style: 'tableHeader' },
      { text: 'Detalhe', style: 'tableHeader' }
    ],
    [
      ...infoRow('Nº Processo', item.numero_processo),
      ...infoRow('Tipo', tipoLabel(item.tipo || item.tipo_licenciamento))
    ],
    [
      ...infoRow('Cliente', item.empresa, { bold: true }),
      ...infoRow('NIF', item.nif_empresa)
    ],
    [
      ...infoRow('Shipper', item.shipper),
      ...infoRow('Descrição', item.descricao)
    ],
    [
      ...infoRow('Criado em', formatDate(item.created_at)),
      ...infoRow('Actualizado em', formatDate(item.updated_at))
    ],
    [
      ...infoRow('Data de Submissão', formatDate(item.data_submissao)),
      ...infoRow('Data de Validade', formatDate(item.data_validade))
    ]
  ]

  content.push({
    table: {
      widths: ['18%', '32%', '18%', '32%'],
      body: infoBody
    },
    layout: {
      hLineWidth: (i) => (i === 0 || i === 1) ? 0.8 : 0.25,
      vLineWidth: () => 0.25,
      hLineColor: () => '#d1d5db',
      vLineColor: () => '#d1d5db',
      fillColor: (rowIndex) => {
        if (rowIndex === 0) return '#1a365d'
        if (rowIndex % 2 === 0) return '#f1f5f9'
        return null
      },
      paddingLeft: () => 8,
      paddingRight: () => 8,
      paddingTop: () => 5,
      paddingBottom: () => 5
    },
    margin: [0, 0, 0, 6]
  })

  // ── HISTÓRICO DE OBSERVAÇÕES ──
  if (historico.length > 0) {
    content.push(sectionBar('Histórico de Observações'))

    const obsHeader = [
      { text: 'Data', style: 'tableHeader' },
      { text: 'Utilizador', style: 'tableHeader' },
      { text: 'Observação', style: 'tableHeader' }
    ]

    const obsData = [obsHeader]
    historico.forEach((h, idx) => {
      const data = parseObsDate(h.valor_novo) || formatDate(h.created_at)
      const user = parseObsUser(h.valor_novo) || 'Sistema'
      const text = parseObsText(h.valor_novo) || h.valor_novo || '—'
      obsData.push([
        { text: data, style: 'fieldName', margin: [0, 2, 0, 2] },
        { text: user, style: 'fieldName', margin: [0, 2, 0, 2] },
        { text, margin: [0, 2, 0, 2] }
      ])
    })

    content.push({
      table: {
        widths: ['18%', '20%', '62%'],
        body: obsData
      },
      layout: {
        hLineWidth: (i) => (i === 0 || i === 1) ? 0.8 : 0.25,
        vLineWidth: () => 0.25,
        hLineColor: () => '#d1d5db',
        vLineColor: () => '#d1d5db',
        fillColor: (rowIndex) => {
          if (rowIndex === 0) return '#1a365d'
          if (rowIndex % 2 === 0) return '#f1f5f9'
          return null
        },
        paddingLeft: () => 8,
        paddingRight: () => 8,
        paddingTop: () => 4,
        paddingBottom: () => 4
      },
      margin: [0, 0, 0, 6]
    })
  }

  // ── LINHA DO TEMPO ──
  if (estadosHistorico.length > 0) {
    content.push(sectionBar('Linha do Tempo do Estado'))

    const estHeader = [
      { text: 'Data', style: 'tableHeader' },
      { text: 'De', style: 'tableHeader' },
      { text: 'Para', style: 'tableHeader' },
      { text: 'Observação', style: 'tableHeader' }
    ]

    const estData = [estHeader]
    estadosHistorico.forEach((e) => {
      estData.push([
        { text: formatDate(e.created_at), style: 'fieldName', margin: [0, 2, 0, 2] },
        { text: estadoLabel(e.estado_anterior) || '—', style: 'fieldName', margin: [0, 2, 0, 2] },
        { text: estadoLabel(e.estado_novo), style: 'fieldName', margin: [0, 2, 0, 2] },
        { text: e.observacao || '—', margin: [0, 2, 0, 2] }
      ])
    })

    content.push({
      table: {
        widths: ['18%', '18%', '20%', '44%'],
        body: estData
      },
      layout: {
        hLineWidth: (i) => (i === 0 || i === 1) ? 0.8 : 0.25,
        vLineWidth: () => 0.25,
        hLineColor: () => '#d1d5db',
        vLineColor: () => '#d1d5db',
        fillColor: (rowIndex) => {
          if (rowIndex === 0) return '#1a365d'
          if (rowIndex % 2 === 0) return '#f1f5f9'
          return null
        },
        paddingLeft: () => 8,
        paddingRight: () => 8,
        paddingTop: () => 4,
        paddingBottom: () => 4
      }
    })
  }

  // ── DOC DEFINITION ──
  const docDefinition = {
    content,
    defaultStyle: {
      fontSize: 10,
      color: '#334155'
    },
    styles: {
      logoText: { fontSize: 18, bold: true, color: '#1a365d' },
      logoSub: { fontSize: 10, color: '#64748b', margin: [0, 1, 0, 0] },
      headerAddress: { fontSize: 7.5, color: '#94a3b8', margin: [0, 1, 0, 0] },
      pageTitle: { fontSize: 14, bold: true, color: '#1a365d', margin: [0, 0, 0, 2] },
      refCode: { fontSize: 11, bold: true, color: '#2563eb' },
      statusBadge: { fontSize: 9, bold: true, alignment: 'center' },
      sectionTitle: { fontSize: 11, bold: true, color: '#1a365d' },
      tableHeader: { fontSize: 8, bold: true, color: '#ffffff' },
      fieldLabel: { fontSize: 8.5, bold: true, color: '#64748b' },
      fieldValue: { fontSize: 8.5, color: '#1e293b' },
      fieldValueBold: { fontSize: 8.5, bold: true, color: '#1e293b' },
      fieldName: { fontSize: 8.5, bold: true, color: '#475569' }
    },
    footer: (currentPage, pageCount) => ({
      stack: [
        {
          canvas: [{ type: 'line', x1: 40, y1: 0, x2: 515, y2: 0, lineWidth: 0.5, lineColor: '#cbd5e1' }],
          margin: [0, 0, 0, 5]
        },
        {
          text: 'FMLider Transitário & Logística',
          style: { fontSize: 8, bold: true, color: '#1a365d' },
          alignment: 'center'
        },
        {
          text: 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul – Cacuaco, Luanda, Angola',
          style: { fontSize: 7, color: '#94a3b8' },
          alignment: 'center'
        },
        {
          text: 'Tel: +244 935 141 747 | Email: geral@fmlider.co.ao | www.fmlider.co.ao',
          style: { fontSize: 7, color: '#94a3b8' },
          alignment: 'center'
        },
        {
          text: `Documento gerado automaticamente em ${formatDateTime(new Date())} — Página ${currentPage} de ${pageCount}`,
          style: { fontSize: 6.5, color: '#b0b8c4', italics: true },
          alignment: 'center',
          margin: [0, 4, 0, 0]
        }
      ],
      margin: [0, 0, 0, 0]
    }),
    pageMargins: [40, 35, 40, 70]
  }

  const pdfMake = await getPdfMake()
  pdfMake.createPdf(docDefinition).download(`FMLider-${item.referencia || 'licenciamento'}.pdf`)
}
