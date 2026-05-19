# Setup & Testing Guide - Chat Pokelu

## Pre-Deployment Checklist

### ✅ Firebase Configuration

#### 1. Firebase Console Setup

```
1. Go to: https://console.firebase.google.com
2. Select project: pokelu-project
3. Enable services:
   - Authentication (Email/Password + Google)
   - Realtime Database
   - Storage (for Cloudinary integration)
```

#### 2. Database Rules

Copy-paste ke Firebase Console → Realtime Database → Rules:

```json
{
    "rules": {
        "users": {
            ".read": "auth != null",
            "$uid": {
                ".write": "auth != null && auth.uid === $uid",
                ".validate": "newData.hasChildren(['name'])",
                "name": {
                    ".validate": "newData.isString() && newData.val().length <= 100"
                },
                "email": {
                    ".validate": "newData.isString() || newData.val() === null"
                },
                "pfp": {
                    ".validate": "newData.isString() || newData.val() === null"
                },
                "status": {
                    ".validate": "newData.isString() || newData.val() === null"
                },
                "lastSeen": {
                    ".validate": "newData.isNumber() || newData.val() === null"
                }
            }
        },

        "user_rooms": {
            "$uid": {
                ".read": "auth != null && auth.uid === $uid",
                ".write": "auth != null && auth.uid === $uid"
            }
        },

        "chats": {
            "$roomId": {
                ".read": "auth != null",

                "messages": {
                    ".indexOn": ["createdAt"],

                    "$msgId": {
                        ".write": "auth != null",

                        ".validate": "newData.hasChildren(['uid','displayName','type','createdAt']) && ( ( newData.child('type').val() === 'text' && newData.child('text').isString() ) || ( newData.child('type').val() === 'image' && newData.child('imageUrl').isString() ) )",

                        "uid": {
                            ".validate": "newData.val() === auth.uid"
                        },

                        "displayName": {
                            ".validate": "newData.isString()"
                        },

                        "text": {
                            ".validate": "newData.isString() || newData.val() === null"
                        },

                        "imageUrl": {
                            ".validate": "newData.isString() || newData.val() === null"
                        },

                        "type": {
                            ".validate": "newData.val() === 'text' || newData.val() === 'image'"
                        },

                        "createdAt": {
                            ".validate": "newData.isNumber()"
                        }
                    }
                }
            }
        }
    }
}
```

### ✅ Cloudinary Setup

#### 1. Create Cloudinary Account

```
1. Go to: https://cloudinary.com
2. Sign up untuk free account
3. Copy credentials:
   - Cloud Name: dsz8bojjy (atau your_cloud_name)
```

#### 2. Create Upload Preset

```
Cloudinary Dashboard → Settings → Upload → Add upload preset

- Preset Name: pokelu_storage
- Unsigned: Toggle ON (untuk client-side upload)
- Folder: pokelu_chat/ (optional, untuk organize)
- Auto-tag: chat
- Format: Auto-detect
- Save
```

#### 3. Update Code

Ganti di `resources/views/chat.blade.php`:

```javascript
// Line ~1150
formData.append("cloud_name", "dsz8bojjy"); // → your_cloud_name
formData.append("upload_preset", "pokelu_storage"); // → your_preset_name

// Line ~1170
xhr.open("POST", "https://api.cloudinary.com/v1_1/dsz8bojjy/auto/upload");
// → https://api.cloudinary.com/v1_1/YOUR_CLOUD_NAME/auto/upload
```

### ✅ Laravel Setup

#### 1. Install Dependencies

```bash
composer install
npm install
```

#### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env`:

```env
APP_NAME="Pokelu"
APP_DEBUG=true  # false in production
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite  # or mysql
DB_DATABASE=database.sqlite

FIREBASE_API=YOUR_FIREBASE_API_KEY  # from Firebase Console
FIREBASE_PROJECT=pokelu-project
```

#### 3. Database Migration (optional)

```bash
php artisan migrate
php artisan db:seed  # if needed
```

### ✅ Development Environment

#### 1. Start Services

```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite build watcher
npm run dev
```

#### 2. Access Application

```
Chat Page: http://localhost:8000/chat
Login Page: http://localhost:8000/login
```

## 🧪 Testing Guide

### Test 1: User Registration & Login

**Steps:**

```
1. Go to http://localhost:8000/login
2. Click REGISTER tab
3. Fill:
   - Nama: Alice
   - Email: alice@test.com
   - Password: Test1234
4. Click Daftar
5. Verify success message
6. Verify user created di Firebase → users/
```

**Expected Results:**

- ✅ User registered di Firebase Auth
- ✅ User data created di `users/{uid}`
- ✅ Redirected to /explore
- ✅ User dapat login

### Test 2: Search User

**Setup:**

- Register 2+ users (Alice, Bob, Charlie)
- Logout dari Alice, Login kembali

**Steps:**

```
1. Open /chat
2. Verify Alice logged in (Firebase console check uid)
3. Type "Bob" di search input
4. Verify Bob tampil di list dengan online status
5. Verify Alice tidak tampil di list (self-excluded)
6. Type "cha"
7. Verify Charlie tampil
8. Clear search input
9. Verify back to chat history (empty jika first time)
```

**Expected Results:**

- ✅ Search realtime, no lag
- ✅ Show only other users
- ✅ Show online/offline status
- ✅ Show email di description

### Test 3: Create Chat Room

**Steps:**

```
1. Search "Bob"
2. Click Bob item
3. Verify chat window open
4. Verify header show "BOB" dengan avatar
5. Verify empty chat state (no messages yet)
6. Check Firebase → chats/ untuk room existence
```

**Expected Results:**

- ✅ Room auto-created
- ✅ Room ID format: uid_alice_uid_bob
- ✅ Room visible di both users' user_rooms/
- ✅ Chat window ready untuk input

### Test 4: Send Text Message

**Steps:**

```
1. Type "Hello Bob!" di input
2. Press Enter
3. Verify message appear di chat (right side)
4. Verify timestamp show
5. Type "How are you?" dan Shift+Enter untuk new line
6. Verify new line dalam message
7. Send message dengan Enter
8. Verify 2 messages terkirim
```

**Expected Results:**

- ✅ Messages appear instantly
- ✅ Order correct (oldest first)
- ✅ Sender avatar show
- ✅ Timestamp accurate
- ✅ Messages saved di Firebase

### Test 5: Upload Image

**Setup:**

- Prepare test image (PNG atau JPG, < 10MB)

**Steps:**

```
1. Click upload button (📎)
2. Select image file
3. Verify progress bar show
4. Wait until 100%
5. Verify image link in Firebase
6. Verify image preview show di chat
7. Verify URL from Cloudinary
```

**Expected Results:**

- ✅ Progress bar update smoothly
- ✅ Image upload ke Cloudinary
- ✅ URL saved di Firebase
- ✅ Image display dengan correct dimensions
- ✅ Lazy loading enabled

### Test 6: Online Status

**Setup:**

- Open 2 browser tabs/windows
- Login different users (Alice di tab1, Bob di tab2)

**Steps:**

```
Tab 1 (Alice):
1. Open /chat
2. Search "Bob"
3. Verify Bob status "● ONLINE"

Tab 2 (Bob):
4. Close browser tab
5. Wait 5 seconds

Tab 1 (Alice):
6. Look at Bob status
7. Should still show online (might take 30s+ untuk Firebase disconnect)
8. Refresh page
9. Verify Bob status changed to "● OFFLINE"
```

**Expected Results:**

- ✅ Status update realtime
- ✅ Green dot untuk online
- ✅ Grey dot untuk offline
- ✅ LastSeen timestamp update

### Test 7: Message History

**Steps:**

```
1. Send 5+ messages antara Alice dan Bob
2. Close /chat page
3. Open /chat lagi
4. Click Bob di sidebar
5. Verify all previous messages masih ada
6. Verify order correct (oldest first)
7. Check message count
```

**Expected Results:**

- ✅ All messages persisted
- ✅ Correct order
- ✅ No message loss
- ✅ Load 100 latest messages

### Test 8: Responsive Design

**Test di berbagai ukuran:**

- 375px (Mobile)
- 768px (Tablet)
- 1024px (Laptop)
- 1920px (Desktop)

**Check:**

- [ ] Sidebar accessible di mobile
- [ ] Chat area fully responsive
- [ ] Input area always visible
- [ ] Messages readable
- [ ] Buttons clickable

**Expected Results:**

- ✅ No horizontal scroll
- ✅ Touch-friendly buttons
- ✅ Readable text sizes

## 🔍 Debug Checklist

### Firebase Issues

**Check Data Structure:**

```javascript
// Di browser console
firebase
    .database()
    .ref("users")
    .once("value", (snap) => {
        console.log("Users:", snap.val());
    });

firebase
    .database()
    .ref("chats")
    .once("value", (snap) => {
        console.log("Chats:", snap.val());
    });
```

**Check Permissions:**

```javascript
// Try write
firebase
    .database()
    .ref("test")
    .set({ hello: "world" })
    .then(() => console.log("Write OK"))
    .catch((e) => console.error("Write failed:", e));
```

### Cloudinary Issues

**Test Upload:**

```javascript
// Di browser console
const formData = new FormData();
formData.append("file" /* file object */);
formData.append("upload_preset", "pokelu_storage");
formData.append("cloud_name", "dsz8bojjy");

fetch("https://api.cloudinary.com/v1_1/dsz8bojjy/auto/upload", {
    method: "POST",
    body: formData,
})
    .then((r) => r.json())
    .then((d) => console.log("URL:", d.secure_url))
    .catch((e) => console.error("Upload failed:", e));
```

### Auth Issues

**Check Current User:**

```javascript
firebase.auth().onAuthStateChanged((user) => {
    console.log("Current user:", user);
    if (user) {
        console.log("UID:", user.uid);
        console.log("Email:", user.email);
        console.log("Name:", user.displayName);
    }
});
```

## 📊 Performance Check

### Load Time

```bash
# Check page load time
# Ideal: < 2s untuk initial load
```

### Network

- Check DevTools → Network tab
- Firebase queries should be < 200ms
- Image load should show progress

### Database

- Check Firebase console → Usage untuk limits
- Verify indexing on createdAt

## 🚀 Deployment

### Production Checklist

```bash
# 1. Build frontend
npm run build

# 2. Update .env
APP_DEBUG=false
APP_URL=https://your-domain.com

# 3. Optimize cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Run migrations
php artisan migrate --force

# 5. Start services
php artisan serve --host=0.0.0.0 --port=8000
```

### Security Checks

- [ ] Firebase rules deployed
- [ ] HTTPS enabled
- [ ] CSRF tokens active
- [ ] Input validation on backend
- [ ] API rate limiting enabled
- [ ] No secrets in code

## 📞 Common Issues & Solutions

### Issue: "Firebase app already initialized"

**Solution:** Clear browser cache/localStorage

```javascript
firebase.apps = [];
// atau reload page
```

### Issue: "CORS error dari Cloudinary"

**Solution:** Check CORS settings di Cloudinary → Security

### Issue: "Messages not syncing realtime"

**Solution:**

```javascript
// Verify listener active
const ref = firebase.database().ref("chats/{roomId}/messages");
ref.on("value", (snap) => console.log("Updated:", snap.val()));
```

### Issue: "Search tak show results"

**Solution:** Verify users/ exist di Firebase

```javascript
firebase
    .database()
    .ref("users")
    .once("value")
    .then((snap) => {
        console.log("Users data:", snap.val());
    });
```

## 📚 Useful Resources

- Firebase Docs: https://firebase.google.com/docs/database
- Cloudinary Docs: https://cloudinary.com/documentation
- Laravel Docs: https://laravel.com/docs
- Firebase Security Rules: https://firebase.google.com/docs/database/security

---

**Last Updated:** 2026-05-19
**Version:** 1.0.0
**Maintainer:** Development Team
