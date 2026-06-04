# FMLider API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

## Response Format

All responses are in JSON format:

```json
{
    "success": true,
    "data": {},
    "message": "Success message"
}
```

Error responses:

```json
{
    "success": false,
    "error": "Error message",
    "code": 400
}
```

---

## Public Endpoints

### Authentication

#### Login
```
POST /auth/login
Content-Type: application/json

{
    "email": "admin@fmlider.co.ao",
    "password": "Admin@2026"
}

Response:
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

#### Register
```
POST /auth/register
Content-Type: application/json

{
    "name": "User Name",
    "email": "user@example.com",
    "password": "Password123"
}

Response:
{
    "success": true,
    "message": "User registered successfully"
}
```

#### Forgot Password
```
POST /auth/forgot-password
Content-Type: application/json

{
    "email": "user@example.com"
}

Response:
{
    "success": true,
    "message": "Password reset link sent to email"
}
```

---

### Services

#### Get All Services
```
GET /services

Response:
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

#### Get Service by ID
```
GET /services/{id}

Response:
{
    "success": true,
    "service": { ... }
}
```

---

### News

#### Get All News
```
GET /news

Query Parameters:
- page: int (default: 1)
- limit: int (default: 10)
- category: string (optional)

Response:
{
    "success": true,
    "news": [ ... ],
    "total": 15,
    "pages": 2
}
```

#### Get News by ID
```
GET /news/{id}

Response:
{
    "success": true,
    "news": { ... }
}
```

---

### Gallery

#### Get All Gallery Items
```
GET /gallery

Query Parameters:
- category: string (trucks, containers, equipment)
- limit: int (default: 20)

Response:
{
    "success": true,
    "gallery": [ ... ]
}
```

---

### Partners

#### Get All Partners
```
GET /partners

Response:
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

### Testimonials

#### Get All Testimonials
```
GET /testimonials

Response:
{
    "success": true,
    "testimonials": [ ... ]
}
```

---

### FAQs

#### Get All FAQs
```
GET /faqs

Query Parameters:
- category: string (optional)

Response:
{
    "success": true,
    "faqs": [ ... ]
}
```

---

### Contacts

#### Submit Contact Form
```
POST /contacts
Content-Type: application/json

{
    "name": "John Doe",
    "company": "Company Name",
    "phone": "+244 935141747",
    "email": "john@example.com",
    "subject": "Inquiry",
    "message": "I would like to know more about..."
}

Response:
{
    "success": true,
    "message": "Contact message received. We will contact you soon."
}
```

---

## Protected Endpoints (Require Authentication)

### Auth - User Profile

#### Get Profile
```
GET /auth/profile
Authorization: Bearer {token}

Response:
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

#### Update Profile
```
PUT /auth/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "New Name",
    "phone": "+244 935141747"
}

Response:
{
    "success": true,
    "message": "Profile updated"
}
```

#### Change Password
```
POST /auth/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "CurrentPassword",
    "new_password": "NewPassword123",
    "new_password_confirm": "NewPassword123"
}

Response:
{
    "success": true,
    "message": "Password changed successfully"
}
```

#### Logout
```
POST /auth/logout
Authorization: Bearer {token}

Response:
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### Admin - Users

#### Get All Users
```
GET /admin/users
Authorization: Bearer {admin_token}

Response:
{
    "success": true,
    "users": [ ... ]
}
```

#### Create User
```
POST /admin/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "name": "New User",
    "email": "newuser@example.com",
    "phone": "+244 935141747",
    "role": "editor",
    "password": "Password123"
}

Response:
{
    "success": true,
    "message": "User created",
    "user": { ... }
}
```

#### Update User
```
PUT /admin/users/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }

Response:
{
    "success": true,
    "message": "User updated"
}
```

#### Delete User
```
DELETE /admin/users/{id}
Authorization: Bearer {admin_token}

Response:
{
    "success": true,
    "message": "User deleted"
}
```

---

### Admin - Services CRUD

#### Create Service
```
POST /admin/services
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "New Service",
    "slug": "new-service",
    "description": "Description",
    "content": "Full content",
    "image": "image.jpg",
    "status": 1
}
```

#### Update Service
```
PUT /admin/services/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }
```

#### Delete Service
```
DELETE /admin/services/{id}
Authorization: Bearer {admin_token}
```

---

### Admin - News CRUD

#### Create News
```
POST /admin/news
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "News Title",
    "slug": "news-slug",
    "image": "image.jpg",
    "description": "Short description",
    "content": "Full content",
    "category": "Investimentos",
    "status": "published"
}
```

#### Update News
```
PUT /admin/news/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }
```

#### Delete News
```
DELETE /admin/news/{id}
Authorization: Bearer {admin_token}
```

---

### Admin - Gallery

#### Upload Image
```
POST /admin/gallery/upload
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

file: [image file]
category: trucks
title: Image Title
alt_text: Alt text
```

#### Create Gallery Item
```
POST /admin/gallery
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Gallery Item",
    "image": "image.jpg",
    "category": "trucks",
    "description": "Description",
    "alt_text": "Alt text"
}
```

#### Delete Gallery Item
```
DELETE /admin/gallery/{id}
Authorization: Bearer {admin_token}
```

---

### Admin - Contacts

#### Get All Contacts
```
GET /admin/contacts
Authorization: Bearer {admin_token}

Query Parameters:
- is_read: 0|1 (optional)
- page: int (default: 1)
```

#### Get Contact Details
```
GET /admin/contacts/{id}
Authorization: Bearer {admin_token}
```

#### Mark as Read
```
PUT /admin/contacts/{id}/mark-read
Authorization: Bearer {admin_token}
```

#### Reply to Contact
```
POST /admin/contacts/{id}/reply
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "reply_message": "Thank you for contacting us..."
}
```

#### Delete Contact
```
DELETE /admin/contacts/{id}
Authorization: Bearer {admin_token}
```

---

### Admin - Partners CRUD

Similar to Services, Partners support:
- `POST /admin/partners` - Create
- `PUT /admin/partners/{id}` - Update
- `DELETE /admin/partners/{id}` - Delete

---

### Admin - Testimonials CRUD

Similar to Services, Testimonials support:
- `POST /admin/testimonials` - Create
- `PUT /admin/testimonials/{id}` - Update
- `DELETE /admin/testimonials/{id}` - Delete

---

### Admin - FAQs CRUD

Similar to Services, FAQs support:
- `POST /admin/faqs` - Create
- `PUT /admin/faqs/{id}` - Update
- `DELETE /admin/faqs/{id}` - Delete

---

### Admin - Banners CRUD

#### Get All Banners
```
GET /admin/banners
Authorization: Bearer {admin_token}
```

#### Create Banner
```
POST /admin/banners
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "title": "Banner Title",
    "description": "Description",
    "image": "banner.jpg",
    "link": "/servicos",
    "status": 1
}
```

#### Update Banner
```
PUT /admin/banners/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{ ... }
```

#### Delete Banner
```
DELETE /admin/banners/{id}
Authorization: Bearer {admin_token}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

---

## Rate Limiting

Currently not implemented but can be added to `/api/config.php`:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 99
X-RateLimit-Reset: 1234567890
```

---

## CORS Headers

All endpoints return these headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## Examples

### JavaScript/Axios

```javascript
// GET request
axios.get('http://localhost:8000/api/services')
    .then(response => console.log(response.data))
    .catch(error => console.error(error));

// POST request with token
axios.post('http://localhost:8000/api/auth/login', {
    email: 'admin@fmlider.co.ao',
    password: 'Admin@2026'
}, {
    headers: { 'Content-Type': 'application/json' }
})
    .then(response => {
        localStorage.setItem('token', response.data.token);
    });

// Request with auth token
const token = localStorage.getItem('token');
axios.get('http://localhost:8000/api/auth/profile', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

### cURL

```bash
# GET request
curl -X GET http://localhost:8000/api/services

# POST request
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@fmlider.co.ao","password":"Admin@2026"}'

# Request with token
curl -X GET http://localhost:8000/api/auth/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

**API Version**: 1.0  
**Last Updated**: January 2024  
**Status**: Active
