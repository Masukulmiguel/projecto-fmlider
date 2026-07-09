import DOMPurify from 'dompurify'

const purifyConfig = {
  ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'a', 'p', 'br', 'ul', 'ol', 'li', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
  ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style', 'title', 'alt'],
  ALLOW_DATA_ATTR: false
}

export const sanitize = (html) => {
  if (!html) return ''
  return DOMPurify.sanitize(html, purifyConfig)
}
