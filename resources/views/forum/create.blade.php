@extends('layout.app')
@section('navbar')
    @include('layout.navbar')
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/forum.css?v=' . time()) }}">
@endpush
@section('content')
<div class="forum-container">
    <a href="{{ route('forum.index') }}" style="color: #ec2dc2; text-decoration: none; display: inline-block; margin-bottom: 20px;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Forum
    </a>

    <div class="forum-form">
        <h2 style="margin-top: 0; margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">Buat Diskusi Baru</h2>
        
        @if($errors->any())
            <div style="background: rgba(255,0,0,0.1); border: 1px solid red; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: red;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('forum.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="title">Judul Diskusi</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Overpower GENGAR">
            </div>

            <div class="form-group">
                <label for="category">Kategori (Opsional)</label>
                <select name="category" id="category" class="form-control">
                    <option value="General">General</option>
                    <option value="Deck Build">Deck Build</option>
                    <option value="Lore">Lore</option>
                    <option value="Trading">Trading</option>
                </select>
            </div>

            <div class="form-group">
                <label for="thumbnail">Gambar Thumbnail</label>
                <input type="file" name="thumbnail" id="thumbnail" class="form-control" accept="image/*">
                <small style="color: rgba(255,255,255,0.5); display: block; margin-top: 5px;">Maksimal 10MB. Disarankan rasio 1:1 atau 4:3.</small>
            </div>

            <div class="form-group">
                <label for="body">Isi Diskusi</label>
                <textarea name="body" id="body" rows="10" class="form-control" required placeholder="Tulis detail diskusi di sini...">{{ old('body') }}</textarea>
            </div>

            <button type="submit" class="btn-submit" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Posting Diskusi</button>
        </form>
    </div>
</div>
@endsection
