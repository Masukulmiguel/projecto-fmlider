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

export async function generateLicenciamentoPdf(item, historico = [], estadosHistorico = []) {
  const logoBase64 = await logoToBase64('/assets/img/logo.jpeg')

  const content = []

  if (logoBase64) {
    content.push({
      image: logoBase64,
      width: 140,
      alignment: 'center',
      margin: [0, 0, 0, 10]
    })
  }

  content.push({
    text: 'FMLider Transitário & Logística',
    style: 'companyName',
    alignment: 'center'
  })

  content.push({
    text: 'Relatório de Licenciamento',
    style: 'title',
    alignment: 'center',
    margin: [0, 5, 0, 15]
  })

  content.push({
    canvas: [{ type: 'line', x1: 0, y1: 0, x2: 515, y2: 0, lineWidth: 1, lineColor: '#1a365d' }],
    margin: [0, 0, 0, 10]
  })

  content.push({
    text: `Referência: ${item.referencia}`,
    style: 'refCode',
    margin: [0, 0, 0, 10]
  })

  const infoData = [
    [{ text: 'Campo', style: 'tableHeader' }, { text: 'Valor', style: 'tableHeader' }],
    [{ text: 'Nº Processo', style: 'fieldName' }, { text: item.numero_processo || '—' }],
    [{ text: 'Estado', style: 'fieldName' }, { text: estadoLabel(item.estado) }],
    [{ text: 'Cliente', style: 'fieldName' }, { text: item.empresa || '—' }],
    [{ text: 'Shipper', style: 'fieldName' }, { text: item.shipper || '—' }],
    [{ text: 'NIF', style: 'fieldName' }, { text: item.nif_empresa || '—' }],
    [{ text: 'Tipo', style: 'fieldName' }, { text: tipoLabel(item.tipo || item.tipo_licenciamento) }],
    [{ text: 'Descrição', style: 'fieldName' }, { text: item.descricao || '—' }],
    [{ text: 'Criado em', style: 'fieldName' }, { text: formatDate(item.created_at) }],
    [{ text: 'Atualizado em', style: 'fieldName' }, { text: formatDate(item.updated_at) }],
    [{ text: 'Data Submissão', style: 'fieldName' }, { text: formatDate(item.data_submissao) }],
    [{ text: 'Data Validade', style: 'fieldName' }, { text: formatDate(item.data_validade) }]
  ]

  content.push({
    text: 'Informações do Licenciamento',
    style: 'sectionTitle',
    margin: [0, 10, 0, 5]
  })

  content.push({
    table: {
      widths: ['35%', '65%'],
      body: infoData
    },
    layout: {
      hLineWidth: (i) => (i === 0 || i === 1) ? 0.5 : 0.25,
      vLineWidth: () => 0.25,
      hLineColor: () => '#cbd5e1',
      vLineColor: () => '#cbd5e1',
      fillColor: (rowIndex) => (rowIndex === 0) ? '#1a365d' : (rowIndex % 2 === 0 ? '#f8fafc' : null),
      paddingLeft: () => 8,
      paddingRight: () => 8,
      paddingTop: () => 4,
      paddingBottom: () => 4
    },
    margin: [0, 0, 0, 10]
  })

  if (historico.length > 0) {
    content.push({
      text: 'Histórico de Observações',
      style: 'sectionTitle',
      margin: [0, 10, 0, 5]
    })

    const obsHeader = [
      { text: 'Utilizador', style: 'tableHeader' },
      { text: 'Observação', style: 'tableHeader' },
      { text: 'Data', style: 'tableHeader' }
    ]

    const obsData = [obsHeader]
    historico.forEach((h) => {
      obsData.push([
        { text: parseObsUser(h.valor_novo) || 'Sistema', style: 'fieldName' },
        { text: parseObsText(h.valor_novo) || h.valor_novo || '—' },
        { text: parseObsDate(h.valor_novo) || formatDate(h.created_at) }
      ])
    })

    content.push({
      table: {
        widths: ['20%', '55%', '25%'],
        body: obsData
      },
      layout: {
        hLineWidth: (i) => (i === 0 || i === 1) ? 0.5 : 0.25,
        vLineWidth: () => 0.25,
        hLineColor: () => '#cbd5e1',
        vLineColor: () => '#cbd5e1',
        fillColor: (rowIndex) => (rowIndex === 0) ? '#1a365d' : (rowIndex % 2 === 0 ? '#f8fafc' : null),
        paddingLeft: () => 8,
        paddingRight: () => 8,
        paddingTop: () => 4,
        paddingBottom: () => 4
      },
      margin: [0, 0, 0, 10]
    })
  }

  if (estadosHistorico.length > 0) {
    content.push({
      text: 'Linha do Tempo do Estado',
      style: 'sectionTitle',
      margin: [0, 10, 0, 5]
    })

    const estHeader = [
      { text: 'Estado Anterior', style: 'tableHeader' },
      { text: 'Novo Estado', style: 'tableHeader' },
      { text: 'Observação', style: 'tableHeader' },
      { text: 'Data', style: 'tableHeader' }
    ]

    const estData = [estHeader]
    estadosHistorico.forEach((e) => {
      estData.push([
        { text: estadoLabel(e.estado_anterior) || '—', style: 'fieldName' },
        { text: estadoLabel(e.estado_novo) },
        { text: e.observacao || '—' },
        { text: formatDate(e.created_at) }
      ])
    })

    content.push({
      table: {
        widths: ['20%', '20%', '35%', '25%'],
        body: estData
      },
      layout: {
        hLineWidth: (i) => (i === 0 || i === 1) ? 0.5 : 0.25,
        vLineWidth: () => 0.25,
        hLineColor: () => '#cbd5e1',
        vLineColor: () => '#cbd5e1',
        fillColor: (rowIndex) => (rowIndex === 0) ? '#1a365d' : (rowIndex % 2 === 0 ? '#f8fafc' : null),
        paddingLeft: () => 8,
        paddingRight: () => 8,
        paddingTop: () => 4,
        paddingBottom: () => 4
      }
    })
  }

  const docDefinition = {
    content,
    defaultStyle: {
      fontSize: 10,
      color: '#334155'
    },
    styles: {
      companyName: { fontSize: 16, bold: true, color: '#1a365d' },
      title: { fontSize: 14, bold: true, color: '#1a365d' },
      refCode: { fontSize: 12, bold: true, color: '#2563eb' },
      sectionTitle: { fontSize: 11, bold: true, color: '#1a365d', margin: [0, 5, 0, 3] },
      tableHeader: { fontSize: 9, bold: true, color: '#ffffff' },
      fieldName: { fontSize: 9, bold: true, color: '#475569' }
    },
    footer: (currentPage, pageCount) => ({
      columns: [
        {
          width: '50%',
          stack: [
            { text: 'FMLider Transitário & Logística', style: { fontSize: 8, bold: true, color: '#1a365d' } },
            { text: 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul - Cacuaco, Luanda, Angola', style: { fontSize: 7, color: '#64748b' } },
            { text: 'Tel: +244 935141747 | Email: geral@fmlider.co.ao', style: { fontSize: 7, color: '#64748b' } }
          ],
          margin: [40, 0, 0, 0]
        },
        {
          width: '50%',
          stack: [
            { text: 'www.fmlider.co.ao', style: { fontSize: 7, color: '#2563eb', link: 'https://fmlider.co.ao' }, alignment: 'right' },
            { text: `Página ${currentPage} de ${pageCount}`, style: { fontSize: 7, color: '#94a3b8' }, alignment: 'right' }
          ],
          margin: [0, 0, 40, 0]
        }
      ],
      margin: [0, 15, 0, 15],
      borderTop: true
    }),
    pageMargins: [40, 40, 40, 80]
  }

  const pdfMake = await getPdfMake()
  pdfMake.createPdf(docDefinition).download(`FMLider-${item.referencia || 'licenciamento'}.pdf`)
}
