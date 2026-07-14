# 📡 FMLider - Documentação da API

## 📋 Informações do Projecto

| Campo | Valor |
|-------|-------|
| 👨‍💻 **Desenvolvedor** | Masukulu Miguel |
| 🏠 **Organização** | CodingLifeDev |
| 📦 **Projecto Pai** | seefast-project |
| 📅 **Início** | Abril 2025 |
| 🏷️ **Versão Actual** | `2.2.1` |
| 🌍 **Idioma** | Português de Portugal (PT-PT) |

---

## 🌐 URL Base

```
http://localhost:8000/api
```

## 🔐 Autenticação

Todos os endpoints protegidos requerem um token Bearer no cabeçalho Authorization:

```
Authorization: Bearer O_TEU_TOKEN_AQUI
```

## 📋 Formato de Resposta

Todas as respostas são em formato JSON:

```json
{
    "success": true,
    "data": {},
    "message": "Mensagem de sucesso"
}
```

Respostas de erro:

```json
{
    "success": false,
    "error": "Mensagem de erro",
    "code": 400
}
```

---

## 🌍 Endpoints Públicos

### 🔐 Autenticação

#### Login
```
POST /auth/login
Content-Type: application/json

{
    "email": "admin@fmlider.co.ao",
    "password": "A_TUA_SENHA"
}

Resposta:
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@fmlider.co.ao",
        "role": "admin"
    }
}
```

#### Registo
```
POST /auth/register
Content-Type: application/json

{
    "name": "Nome do Utilizador",
    "email": "utilizador@exemplo.com",
    "password": "Senha123"
}

Resposta:
{
    "success": true,
    "message": "Utilizador registado com sucesso"
}
```

#### Esqueci a Senha
```
POST /auth/forgot-password
Content-Type: application/json

{
    "email": "utilizador@exemplo.com"
}

Resposta:
{
    "success": true,
    "message": "Link de redefinição de senha enviado para o email"
}
```

---

### 🔧 Serviços

#### Obter Todos os Serviços
```
GET /services

Resposta:
{
    "success": true,
    "services": [
        {
            "id": 1,
            "title": "Desembaraço Aduaneiro",
            "slug": "desembaraco-aduaneiro",
            "description": "...",
            "image": "service-aduaneiro.jpg",
            "status": 1
        }
    ]
}
```

#### Obter Serviço por ID
```
GET /services/{id}

Resposta:
{
    "success": true,
    "service": { ... }
}
```

---

### 📰 Notícias

#### Obter Todas as Notícias
```
GET /news

Parâmetros de Query:
- page: int (predefinido: 1)
- limit: int (predefinido: 10)
- category: string (opcional)

Resposta:
{
    "success": true,
    "news": [ ... ],
    "total": 15,
    "pages": 2
}
```

#### Obter Notícia por ID
```
GET /news/{id}

Resposta:
{
    "success": true,
    "news": { ... }
}
```

---

### 🖼️ Galeria

#### Obter Todos os Itens da Galeria
```
GET /gallery

Parâmetros de Query:
- category: string (trucks, containers, equipment)
- limit: int (predefinido: 20)

Resposta:
{
    "success": true,
    "gallery": [ ... ]
}
```

---

### 🤝 Parceiros

#### Obter Todos os Parceiros
```
GET /partners

Resposta:
{
    "success": true,
    "partners": [
        {
            "id": 1,
            "name": "DHL",
            "logo": "partner1.webp",
            "website": "https://www.dhl.com"
        }
    ]
}
```

---

### ⭐ Testemunhos

#### Obter Todos os Testemunhos
```
GET /testimonials

Resposta:
{
    "success": true,
    "testimonials": [ ... ]
}
```

---

### ❓ FAQs

#### Obter Todas as FAQs
```
GET /faqs

Parâmetros de Query:
- category: string (opcional)

Resposta:
{
    "success": true,
    "faqs": [ ... ]
}
```

---

### 📞 Contactos

#### Enviar Formulário de Contacto
```
POST /contacts
Content-Type: application/json

{
    "name": "João Silva",
    "company": "Nome da Empresa",
    "phone": "+244 935141747",
    "email": "joao@exemplo.com",
    "subject": "Pedido de Informação",
    "message": "Gostaria de saber mais sobre..."
}

Resposta:
{
    "success": true,
    "message": "Mensagem de contacto recebida. Entraremos em contacto em breve."
}
```

---

## 🔒 Endpoints Protegidos (Requerem Autenticação)

### 🔐 Auth - Perfil do Utilizador

#### Obter Perfil
```
GET /auth/profile
Authorization: Bearer {token}

Resposta:
{
    "success": true,
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@fmlider.co.ao",
        "phone": "+244 935141747",
        "role": "admin",
        "created_at": "2024-01-01T12:00:00Z"
    }
}
```

#### Actualizar Perfil
```
PUT /auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Novo Nome",
    "phone": "+244 935141747"
}

Resposta:
{
    "success": true,
    "message": "Perfil actualizado"
}
```

#### Alterar Senha
```
POST /auth/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "SenhaAtual",
    "new_password": "NovaSenha123",
    "new_password_confirm": "NovaSenha123"
}

Resposta:
{
    "success": true,
    "message": "Senha alterada com sucesso"
}
```

#### Logout
```
POST /auth/logout
Authorization: Bearer {token}

Resposta:
{
    "success": true,
    "message": "Sessão terminada com sucesso"
}
```

---

### 🛡️ Admin - Utilizadores

#### Obter Todos os Utilizadores
```
GET /admin/users
Authorization: Bearer {admin_token}

Resposta:
{
    "success": true,
    "users": [ ... ]
}
```

#### Criar Utilizador
```
POST /admin/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "Novo Utilizador",
    "email": "novoutilizador@exemplo.com",
    "phone": "+244 935141747",
    "role": "editor",
    "password": "Senha123"
}

Resposta:
{
    "success": true,
    "message": "Utilizador criado",
    "user": { ... }
}
```

#### Actualizar Utilizador
```
PUT /admin/users/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }

Resposta:
{
    "success": true,
    "message": "Utilizador actualizado"
}
```

#### Eliminar Utilizador
```
DELETE /admin/users/{id}
Authorization: Bearer {admin_token}

Resposta:
{
    "success": true,
    "message": "Utilizador eliminado"
}
```

---

### 🛡️ Admin - CRUD de Serviços

#### Criar Serviço
```
POST /admin/services
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Novo Serviço",
    "slug": "novo-servico",
    "description": "Descrição",
    "content": "Conteúdo completo",
    "image": "image.jpg",
    "status": 1
}
```

#### Actualizar Serviço
```
PUT /admin/services/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }
```

#### Eliminar Serviço
```
DELETE /admin/services/{id}
Authorization: Bearer {admin_token}
```

---

### 🛡️ Admin - CRUD de Notícias

#### Criar Notícia
```
POST /admin/news
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Título da Notícia",
    "slug": "slug-da-noticia",
    "image": "image.jpg",
    "description": "Descrição curta",
    "content": "Conteúdo completo",
    "category": "Investimentos",
    "status": "published"
}
```

#### Actualizar Notícia
```
PUT /admin/news/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }
```

#### Eliminar Notícia
```
DELETE /admin/news/{id}
Authorization: Bearer {admin_token}
```

---

### 🛡️ Admin - Galeria

#### Upload de Imagem
```
POST /admin/gallery/upload
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

file: [ficheiro de imagem]
category: trucks
title: Título da Imagem
alt_text: Texto alternativo
```

#### Criar Item da Galeria
```
POST /admin/gallery
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Item da Galeria",
    "image": "image.jpg",
    "category": "trucks",
    "description": "Descrição",
    "alt_text": "Texto alternativo"
}
```

#### Eliminar Item da Galeria
```
DELETE /admin/gallery/{id}
Authorization: Bearer {admin_token}
```

---

### 🛡️ Admin - Contactos

#### Obter Todos os Contactos
```
GET /admin/contacts
Authorization: Bearer {admin_token}

Parâmetros de Query:
- is_read: 0|1 (opcional)
- page: int (predefinido: 1)
```

#### Obter Detalhes do Contacto
```
GET /admin/contacts/{id}
Authorization: Bearer {admin_token}
```

#### Marcar como Lido
```
PUT /admin/contacts/{id}/mark-read
Authorization: Bearer {admin_token}
```

#### Responder ao Contacto
```
POST /admin/contacts/{id}/reply
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "reply_message": "Obrigado por nos contactar..."
}
```

#### Eliminar Contacto
```
DELETE /admin/contacts/{id}
Authorization: Bearer {admin_token}
```

---

### 🛡️ Admin - CRUD de Parceiros

Semelhante aos Serviços, os Parceiros suportam:
- `POST /admin/partners` - Criar
- `PUT /admin/partners/{id}` - Actualizar
- `DELETE /admin/partners/{id}` - Eliminar

---

### 🛡️ Admin - CRUD de Testemunhos

Semelhante aos Serviços, os Testemunhos suportam:
- `POST /admin/testimonials` - Criar
- `PUT /admin/testimonials/{id}` - Actualizar
- `DELETE /admin/testimonials/{id}` - Eliminar

---

### 🛡️ Admin - CRUD de FAQs

Semelhante aos Serviços, as FAQs suportam:
- `POST /admin/faqs` - Criar
- `PUT /admin/faqs/{id}` - Actualizar
- `DELETE /admin/faqs/{id}` - Eliminar

---

### 🛡️ Admin - CRUD de Banners

#### Obter Todos os Banners
```
GET /admin/banners
Authorization: Bearer {admin_token}
```

#### Criar Banner
```
POST /admin/banners
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Título do Banner",
    "description": "Descrição",
    "image": "banner.jpg",
    "link": "/servicos",
    "status": 1
}
```

#### Actualizar Banner
```
PUT /admin/banners/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }
```

#### Eliminar Banner
```
DELETE /admin/banners/{id}
Authorization: Bearer {admin_token}
```

---

## 📋 Códigos de Erro

| Código | Descrição |
|--------|-----------|
| 200 | Sucesso |
| 201 | Criado |
| 400 | Pedido Inválido |
| 401 | Não Autorizado |
| 403 | Proibido |
| 404 | Não Encontrado |
| 422 | Erro de Validação |
| 500 | Erro Interno do Servidor |

---

## 🛡️ Rate Limiting

Actualmente não implementado mas pode ser adicionado a `/api/config.php`:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 99
X-RateLimit-Reset: 1234567890
```

---

## 🌐 Headers CORS

Todos os endpoints devolvem estes headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## 📝 Exemplos

### JavaScript/Axios

```javascript
// 📡 Pedido GET
axios.get('http://localhost:8000/api/services')
    .then(response => console.log(response.data))
    .catch(error => console.error(error));

// 📡 Pedido POST com token
axios.post('http://localhost:8000/api/auth/login', {
    email: 'admin@fmlider.co.ao',
    password: 'ADMIN_PASSWORD'
}, {
    headers: { 'Content-Type': 'application/json' }
})
    .then(response => {
        localStorage.setItem('token', response.data.token);
    });

// 📡 Pedido com token de auth
const token = localStorage.getItem('token');
axios.get('http://localhost:8000/api/auth/profile', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

### cURL

```bash
# 📡 Pedido GET
curl -X GET http://localhost:8000/api/services

# 📡 Pedido POST
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@fmlider.co.ao","password":"Admin@2026"}'

# 📡 Pedido com token
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer O_TEU_TOKEN_AQUI"
```

---

**🏷️ Versão da API**: `2.2.1`  
**📅 Última Actualização**: Julho 2025  
**📊 Estado**: Activo  
**👨‍💻 Desenvolvido por**: Masukulu Miguel (CodingLifeDev) para o projecto **seefast-project**
