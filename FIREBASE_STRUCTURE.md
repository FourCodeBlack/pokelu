# Firebase Realtime Database Structure

Dokumentasi struktur database Firebase untuk fitur chat Pokelu.

## Struktur Database

```
pokelu-project-rtdb/
├── users/
│   ├── {uid}
│   │   ├── uid: string                    # User ID (dari Firebase Auth)
│   │   ├── name: string                   # Nama user
│   │   ├── email: string                  # Email user
│   │   ├── pfp: string                    # Profile picture key (pfp1, pfp2, pfp3, etc)
│   │   ├── role: string                   # User role (user, admin)
│   │   ├── status: string                 # online | offline
│   │   ├── lastSeen: timestamp            # Terakhir kali online
│   │   └── createdAt: timestamp           # Waktu registrasi
│   │
│   └── {uid2}
│       └── ...
│
├── chats/
│   ├── {roomId}                           # roomId format: uid1_uid2 (sorted lexicographically)
│   │   ├── createdAt: timestamp           # Waktu room dibuat
│   │   ├── participants: object          # { uid1: true, uid2: true }
│   │   └── messages/
│   │       ├── {msgId}
│   │       │   ├── uid: string            # User ID pengirim
│   │       │   ├── displayName: string    # Nama pengirim
│   │       │   ├── photoURL: string       # PFP key pengirim
│   │       │   ├── text: string           # Isi pesan (jika text message)
│   │       │   ├── imageUrl: string       # URL gambar (jika image message)
│   │       │   ├── fileUrl: string        # URL file (jika file message)
│   │       │   ├── fileName: string       # Nama file (jika file message)
│   │       │   └── createdAt: timestamp   # Waktu pesan dikirim
│   │       │
│   │       └── {msgId2}
│   │           └── ...
│   │
│   └── {roomId2}
│       └── ...
│
└── user_rooms/
    ├── {uid}
    │   ├── {roomId}
    │   │   ├── partnerId: string          # UID partner chat
    │   │   ├── name: string               # Nama partner
    │   │   ├── avatar: string             # PFP key partner
    │   │   ├── lastMsg: string            # Preview pesan terakhir
    │   │   └── lastTs: timestamp          # Waktu pesan terakhir
    │   │
    │   └── {roomId2}
    │       └── ...
    │
    └── {uid2}
        └── ...
```

## Rules (Firebase Security Rules)

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

## Cloudinary Setup

**Upload Preset Configuration:**

- Cloud Name: `dsz8bojjy`
- Upload Preset: `pokelu_storage`
- Max File Size: 10MB
- Allowed Types: Image (JPEG, PNG, WebP, GIF) & PDF

**Upload URL:**

```
https://api.cloudinary.com/v1_1/dsz8bojjy/auto/upload
```

## Message Types

### 1. Text Message

```json
{
    "uid": "user_123",
    "displayName": "John Doe",
    "photoURL": "pfp1",
    "text": "Hello!",
    "createdAt": 1234567890
}
```

### 2. Image Message

```json
{
    "uid": "user_123",
    "displayName": "John Doe",
    "photoURL": "pfp1",
    "imageUrl": "https://res.cloudinary.com/dsz8bojjy/image/upload/...",
    "text": "Look at this!", // Optional caption
    "createdAt": 1234567890
}
```

### 3. File Message

```json
{
    "uid": "user_123",
    "displayName": "John Doe",
    "photoURL": "pfp1",
    "fileUrl": "https://res.cloudinary.com/dsz8bojjy/raw/upload/...",
    "fileName": "document.pdf",
    "createdAt": 1234567890
}
```

## Room ID Generation

Room ID harus unik dan konsisten untuk setiap pasangan user:

```javascript
// Format: uid1_uid2 (sorted lexicographically)
const uids = [uid1, uid2].sort();
const roomId = `${uids[0]}_${uids[1]}`;
```

Contoh:

- User A (uid: `alice123`) dan User B (uid: `bob456`)
- Room ID: `alice123_bob456` (tidak peduli siapa yang mulai, ID-nya sama)

## Status Online/Offline Tracking

1. User set status "online" saat login
2. Firebase `onDisconnect` otomatis set status "offline" saat koneksi loss
3. User update `lastSeen` timestamp setiap aktivitas
4. Client track user status dari value listener di users/

Contoh:

```javascript
// Set online saat login
db.ref(`users/${uid}`).update({
    status: "online",
    lastSeen: firebase.database.ServerValue.TIMESTAMP,
});

// Set offline saat disconnect
db.ref(`users/${uid}`).onDisconnect().update({
    status: "offline",
    lastSeen: firebase.database.ServerValue.TIMESTAMP,
});
```

## Performance Optimization

1. **PFP Caching**: Cache profile pictures di `pfpCache` object untuk menghindari multiple reads
2. **Message Pagination**: Query hanya 100 pesan terakhir dengan `limitToLast(100)`
3. **Batch Updates**: Update `lastMsg` untuk kedua user dalam single transaction saat possible
4. **Lazy Loading**: Load profile picture hanya saat dibutuhkan
5. **Index**: Pastikan `messages` collection di-index oleh `createdAt` untuk performa query

## Testing

### Test Data untuk Development

```javascript
// User 1
{
  uid: "user_alice",
  email: "alice@example.com",
  name: "Alice",
  pfp: "pfp1",
  status: "online",
  createdAt: new Date().toISOString()
}

// User 2
{
  uid: "user_bob",
  email: "bob@example.com",
  name: "Bob",
  pfp: "pfp2",
  status: "online",
  createdAt: new Date().toISOString()
}

// Test Room: alice123_bob456
{
  createdAt: timestamp,
  participants: {
    "user_alice": true,
    "user_bob": true,
  },
  messages: {
    msg1: {
      uid: "user_alice",
      displayName: "Alice",
      text: "Hello Bob!",
      createdAt: timestamp
    }
  }
}
```
