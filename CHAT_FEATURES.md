# Fitur Chat Pokelu - Dokumentasi Implementasi

## Overview

Sistem chat Pokelu menggunakan Firebase Realtime Database untuk messaging, dengan dukungan:

- ✅ Search user realtime
- ✅ Automatic room creation
- ✅ Text messages
- ✅ Image upload to Cloudinary
- ✅ File sharing
- ✅ Online/offline status
- ✅ Message history
- ✅ Last message preview di sidebar

## File yang Dimodifikasi

### 1. **resources/views/chat.blade.php**

Main chat page dengan semua fitur integrated.

**Perubahan:**

- ✅ Tambah `loadAllUsers()` - fetch semua user dari Firebase
- ✅ Tambah `setupUserStatusListener()` - track online/offline status
- ✅ Tambah search functionality dengan realtime filter
- ✅ Tambah `createOrOpenRoom()` - automatic room creation
- ✅ Tambah `uploadToCloudinary()` - upload file to Cloudinary
- ✅ Update `appendMessage()` - support images dan files
- ✅ Update `sendMessage()` - handle file uploads
- ✅ Update key listeners - Enter untuk send, Shift+Enter untuk baris baru

### 2. **app/Http/Controllers/AuthController.php**

Firebase auth controller untuk sync user data.

**Perubahan:**

- ✅ Improve `firebaseLogin()` method - create/update user dengan complete data
- ✅ Add fields: uid, status, lastSeen, createdAt
- ✅ Set default avatar (pfp3)

## Fitur Detail

### 1. Search User Realtime

**Cara Kerja:**

1. User ketik di search input di sidebar
2. `userSearchInput` event listener trigger `loadAllUsers()` results
3. Filter user berdasarkan: nama, email, atau uid
4. Tampilkan dengan online/offline status
5. Click user → otomatis buka/buat room

**Code Reference:**

```javascript
// Di chat.blade.php line ~190
document
    .getElementById("userSearchInput")
    .addEventListener("input", function (e) {
        const query = this.value.trim().toLowerCase();
        // ... filter logic
    });

function renderSearchResults() {
    // ... render user list
}
```

### 2. Automatic Room Creation

**Cara Kerja:**

1. User pilih dari search results
2. `createOrOpenRoom(partnerId, partnerName, partnerPfp)` di-trigger
3. Generate unique roomId: `uid1_uid2` (sorted)
4. Cek apakah room sudah ada
5. Jika tidak ada, create di `chats/{roomId}`
6. Add room ke `user_rooms` untuk kedua user
7. Open chat window

**Room ID Generation:**

```javascript
// Ensure consistent room ID regardless of who initiates
const uids = [currentUser.uid, partnerId].sort();
const roomId = `${uids[0]}_${uids[1]}`;
```

### 3. Image Upload to Cloudinary

**Cara Kerja:**

1. User click upload button
2. Select file (image atau file)
3. File di-upload ke Cloudinary dengan progress tracking
4. Dapatkan secure URL
5. Save message dengan imageUrl atau fileUrl
6. Show preview gambar di chat

**Validation:**

- Max file size: 10MB
- Supported types: JPG, PNG, WebP, GIF, PDF
- Upload progress visual feedback

**Code Reference:**

```javascript
async function uploadToCloudinary(file) {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("upload_preset", "pokelu_storage");
    formData.append("cloud_name", "dsz8bojjy");

    // XHR dengan progress tracking
    // Return secure_url dari response
}
```

### 4. Message Types

#### Text Message

```json
{
  "uid": "user_id",
  "displayName": "User Name",
  "photoURL": "pfp1",
  "text": "Hello!",
  "createdAt": timestamp
}
```

#### Image Message

```json
{
  "uid": "user_id",
  "displayName": "User Name",
  "photoURL": "pfp1",
  "imageUrl": "https://res.cloudinary.com/...",
  "text": "Optional caption",  // Bisa kosong
  "createdAt": timestamp
}
```

#### File Message

```json
{
  "uid": "user_id",
  "displayName": "User Name",
  "photoURL": "pfp1",
  "fileUrl": "https://res.cloudinary.com/...",
  "fileName": "document.pdf",
  "createdAt": timestamp
}
```

### 5. Online Status Tracking

**Implementasi:**

1. Saat user login → set status "online"
2. Firebase `onDisconnect` trigger → set status "offline"
3. `lastSeen` update dengan timestamp
4. Client listen ke `users/` untuk track status realtime
5. UI show green dot untuk online, grey untuk offline

```javascript
// Set online saat login
db.ref(`users/${currentUser.uid}`).update({
    status: "online",
    lastSeen: firebase.database.ServerValue.TIMESTAMP,
});

// Auto offline pada disconnect
const onDisconnect = db.ref(`users/${currentUser.uid}`).onDisconnect();
onDisconnect.update({
    status: "offline",
    lastSeen: firebase.database.ServerValue.TIMESTAMP,
});
```

## Usage Guide

### Untuk Developer

#### 1. Setup Firebase

- Create Firebase project di console.firebase.google.com
- Enable Realtime Database
- Update credentials di `layout/app.blade.php` dan `layout/login.blade.php`
- Deploy security rules dari FIREBASE_STRUCTURE.md

#### 2. Setup Cloudinary

- Create Cloudinary account
- Create upload preset: `pokelu_storage`
- Update cloud name di code: `dsz8bojjy` → your_cloud_name

#### 3. Run Project

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run development server
php artisan serve
npm run dev
```

#### 4. Build untuk Production

```bash
npm run build
```

### Untuk User

1. **Login/Register** → Go to login page
2. **Buka Chat** → Click chat link di navbar
3. **Cari User** → Type di search box (nama/email/uid)
4. **Kirim Pesan** → Type message, press Enter atau Shift+Enter
5. **Kirim Gambar** → Click upload button, select image, send
6. **Lihat History** → Click user di sidebar untuk lihat chat history

## Keyboard Shortcuts

| Shortcut        | Action              |
| --------------- | ------------------- |
| `Enter`         | Send message        |
| `Shift+Enter`   | New line in message |
| `Esc` (planned) | Close chat          |

## Performance Tips

1. **Caching**: PFP di-cache untuk menghindari multiple reads
2. **Lazy Loading**: Load messages hanya saat room dibuka
3. **Batch Updates**: Update lastMsg untuk kedua user sekaligus
4. **Index**: Database di-index pada createdAt untuk fast queries
5. **Pagination**: Load 100 pesan terakhir dengan `limitToLast(100)`

## Troubleshooting

### Problem: Pesan tidak terkirim

**Solution:**

- Cek Firebase connection
- Verify uid di database
- Check room ID format (uid1_uid2 sorted)
- See browser console untuk error details

### Problem: Image tidak terlihat

**Solution:**

- Verify Cloudinary credentials
- Check file size < 10MB
- Verify imageUrl saved correctly di database
- Check image CORS settings

### Problem: Search tidak berfungsi

**Solution:**

- Verify users/ data di Firebase
- Check user not filtering self
- Verify search results render
- Check console untuk error

### Problem: Status offline tidak update

**Solution:**

- Check onDisconnect rules di Firebase
- Verify user_rooms listener active
- Check timestamp updates

## File Structure

```
resources/
├── views/
│   ├── chat.blade.php          # Main chat UI + logic
│   └── layout/
│       ├── app.blade.php        # Firebase init
│       └── login.blade.php      # Auth logic
├── js/
│   └── bootstrap.js
└── css/
    └── app.css

app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php   # Firebase auth
│   │   ├── ChatController.php   # Chat routes
│   │   └── ...
│   └── Models/
│       └── FirebaseHelper.php   # Firebase utilities

routes/
└── web.php                       # Chat routes

config/
└── firebase.php                 # Firebase config

FIREBASE_STRUCTURE.md            # DB structure docs
CHAT_FEATURES.md                 # This file
```

## API Endpoints

### Chat Routes (Laravel)

```
GET  /chat                       # Display chat page
GET  /explore                    # Go back to explore
```

### Firebase Collections (Frontend only)

```
GET  /users/                     # All users
GET  /chats/{roomId}/messages    # Room messages
GET  /user_rooms/{uid}           # User's rooms
```

## Next Features (Future)

- [ ] Group chat support
- [ ] Message edit/delete
- [ ] Message reactions (emoji)
- [ ] Voice messages
- [ ] Video call integration
- [ ] Message search
- [ ] User blocking
- [ ] Message encryption
- [ ] Typing indicators
- [ ] Read receipts

## Security Considerations

1. **Firebase Rules**: Enforce per-user access control
2. **File Validation**: Check file type dan size di client
3. **CORS**: Configure Cloudinary CORS settings
4. **Rate Limiting**: Implement on backend jika needed
5. **SQL Injection**: Use parameterized queries di backend
6. **XSS Prevention**: Always escape HTML output

## Support & Contact

For issues atau questions, silakan buat issue di GitHub atau contact development team.

---

**Last Updated:** 2026-05-19
**Version:** 1.0.0
**Status:** Production Ready ✅
