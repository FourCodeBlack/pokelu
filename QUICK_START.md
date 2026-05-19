# Quick Start Guide - Pokelu Chat System

## 🚀 Rapid Setup (5 minutes)

### Prerequisites

- PHP 8.1+
- Node.js 18+
- Firebase account
- Cloudinary account

### Step 1: Clone & Install

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate
```

### Step 2: Firebase Setup

1. Go to https://console.firebase.google.com
2. Select project: **pokelu-project**
3. Realtime Database → Deploy rules from `FIREBASE_STRUCTURE.md`
4. Get credentials and update:
    - `resources/views/layout/app.blade.php`
    - `resources/views/layout/login.blade.php`

### Step 3: Cloudinary Setup

1. Go to https://cloudinary.com
2. Create upload preset: `pokelu_storage`
3. Note cloud name: (will be in credentials)
4. Update in `resources/views/chat.blade.php`:
    - Line ~1150: `dsz8bojjy` → your_cloud_name
    - Line ~1170: same cloud_name

### Step 4: Run

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Visit: http://localhost:8000/chat

---

## 📋 File Guide

### Main Files

| File                                      | Purpose         | Status     |
| ----------------------------------------- | --------------- | ---------- |
| `resources/views/chat.blade.php`          | Chat UI + logic | ✅ Updated |
| `app/Http/Controllers/AuthController.php` | Firebase auth   | ✅ Updated |
| `resources/views/layout/app.blade.php`    | Firebase init   | ✅ Exists  |
| `routes/web.php`                          | Routes          | ✅ Exists  |

### Documentation

| File                        | Purpose              |
| --------------------------- | -------------------- |
| `FIREBASE_STRUCTURE.md`     | DB structure & rules |
| `CHAT_FEATURES.md`          | Feature docs         |
| `SETUP_TESTING_GUIDE.md`    | Complete guide       |
| `FILE_CHANGES_SUMMARY.md`   | Changes summary      |
| `IMPLEMENTATION_SUMMARY.md` | Overview             |

---

## 🔧 Configuration

### Firebase Config (app.blade.php)

```javascript
firebase.initializeApp({
    apiKey: "YOUR_API_KEY",
    authDomain: "pokelu-project.firebaseapp.com",
    databaseURL:
        "https://pokelu-project-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "pokelu-project",
    storageBucket: "pokelu-project.firebasestorage.app",
    messagingSenderId: "210207641471",
    appId: "1:210207641471:web:aff0df7b7b3acb0ce2d44a",
});
```

### Cloudinary Config (chat.blade.php)

```javascript
formData.append("cloud_name", "dsz8bojjy"); // Your cloud name
formData.append("upload_preset", "pokelu_storage"); // Your preset
```

---

## ✨ Features

### 1. User Search

- Type user name, email, or uid
- See online/offline status
- Click to start chat

### 2. Chat

- Text messages
- Image upload (preview)
- File sharing
- Message history

### 3. Upload

- Images to Cloudinary
- Progress tracking
- Max 10MB
- Auto save URL

### 4. Status

- Online/offline indicator
- Last seen timestamp
- Auto sync realtime

---

## 🧪 Quick Test

### Test 1: Register

1. Go to `/login`
2. Register new user
3. Verify created in Firebase

### Test 2: Search

1. Register 2+ users
2. Open `/chat`
3. Type user name in search
4. See results realtime

### Test 3: Send Message

1. Click user from search
2. Type message
3. Press Enter
4. Verify appears in chat

### Test 4: Upload Image

1. Click upload button
2. Select image
3. See progress bar
4. Image preview appears

---

## 🐛 Troubleshooting

### Firebase connection fails

```javascript
// Check in console
firebase.apps; // Should show your app
firebase.auth().currentUser; // Should show logged user
```

### Cloudinary upload fails

- Check upload preset exists
- Verify cloud name correct
- Check file size < 10MB

### Search shows no results

- Verify users/ data in Firebase
- Check user not filtering self
- Refresh page

### Messages not loading

- Check Firebase rules
- Verify room exists in database
- Check listener active in console

---

## 📚 Documentation Links

- Firebase: https://firebase.google.com/docs/database
- Cloudinary: https://cloudinary.com/documentation
- Laravel: https://laravel.com/docs

---

## ✅ Production Checklist

Before deploy:

- [ ] Firebase rules deployed
- [ ] Cloudinary configured
- [ ] Environment variables set
- [ ] HTTPS enabled
- [ ] Database backed up
- [ ] Test all features

---

## 🎯 Next Steps

1. **Complete Setup**: Follow SETUP_TESTING_GUIDE.md
2. **Manual Testing**: 8 test scenarios provided
3. **Deploy**: Follow deployment section
4. **Monitor**: Check logs & Firebase usage

---

## 📞 Support

- Check documentation files
- View browser console for errors
- Check Laravel logs: `storage/logs/`
- Firebase console for data issues

---

**Ready to chat? Start at `/chat` 🚀**
