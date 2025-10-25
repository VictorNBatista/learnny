document.addEventListener('DOMContentLoaded', () => {
  carregarMaterias()

  document
    .getElementById('create-class')
    .addEventListener('submit', cadastrarProfessor)
})

async function carregarMaterias() {
  const container = document.getElementById('subjects-checkbox-list')

  try {
    const resposta = await fetch('http://localhost:8000/api/subject/listar')
    if (!resposta.ok) throw new Error('Erro ao carregar matérias')

    const materias = await resposta.json()
    console.log('Matérias carregadas:', materias)

    materias.forEach(subject => {
      const wrapper = document.createElement('div')
      wrapper.classList.add('subject-item')

      const checkbox = document.createElement('input')
      checkbox.type = 'checkbox'
      checkbox.name = 'subjects[]'
      checkbox.value = subject.id
      checkbox.id = `subject-${subject.id}`

      const label = document.createElement('label')
      label.htmlFor = checkbox.id
      label.textContent = subject.name

      // Anexa os dois ao wrapper
      wrapper.appendChild(checkbox)
      wrapper.appendChild(label)

      // Adiciona o wrapper no container
      container.appendChild(wrapper)
    })
  } catch (erro) {
    console.error('Erro ao carregar matérias:', erro)
  }
}

async function cadastrarProfessor(event) {
  event.preventDefault()

  const name = document.getElementById('name').value
  const email = document.getElementById('email').value
  const password = document.getElementById('password').value
  const password_confirmation = document.getElementById(
    'password_confirmation'
  ).value
  const photo_url = document.getElementById('avatar').value
  const contact = document.getElementById('whatsapp').value
  const biography = document.getElementById('bio').value
  const price = parseFloat(document.getElementById('cost').value)

  const subjects = Array.from(
    document.querySelectorAll('input[name="subjects[]"]:checked')
  ).map(cb => parseInt(cb.value))

  const dados = {
    name,
    email,
    password,
    password_confirmation,
    photo_url,
    contact,
    biography,
    price,
    subjects
  }

  console.log('Dados a serem enviados:', dados) // 👈 log dos dados
  try {
    const resposta = await fetch(
      'http://localhost:8000/api/professor/cadastrar',
      {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify(dados)
      }
    )

    const tipoResposta = resposta.headers.get('Content-Type')
    if (!tipoResposta || !tipoResposta.includes('application/json')) {
      const textoErro = await resposta.text()
      console.error('Erro inesperado (não é JSON):', textoErro) // 👈 log no console
      alert(`Erro inesperado: ${textoErro}`)
      return
    }

    const respostaJson = await resposta.json()
    if (resposta.ok) {
      alert(`Professor ${dados.name} cadastrado com sucesso!`)
      document.getElementById('create-class').reset()
    } else {
      console.error('Erro da API:', respostaJson) // 👈 log detalhado do erro da API
      alert(`Erro: ${respostaJson.message || 'Falha ao cadastrar professor'}`)
    }
  } catch (erro) {
    console.error('Erro na requisição:', erro) // 👈 log do erro no try/catch
    alert(`Erro: ${erro.message}`)
  }
}
