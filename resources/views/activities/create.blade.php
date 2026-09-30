@extends('layouts.site')

@section('title', 'สร้างกิจกรรม - KKU FitCom')

@section('styles')
<style>
    .act-wrap { max-width: 640px; margin: 0 auto; padding: 0 4px; }
    .back-link { display: inline-block; margin-bottom: 12px; color: #3a9fb0; text-decoration: none; font-size: 14px; }
    .act-wrap h1 { font-size: 20px; margin-bottom: 16px; }

    .err-list { background: #fdeaea; color: #d9534f; padding: 12px 20px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }

    .form-card { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 16px; }
    .form-card h2 { font-size: 15px; color: #3a9fb0; margin-bottom: 12px; }

    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: 13px; color: #555; margin-bottom: 4px; }
    .field input[type="text"], .field input[type="date"], .field input[type="time"],
    .field input[type="number"], .field select, .field textarea {
        width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit;
    }
    .field textarea {
        resize: vertical;
        max-height: 160px;
        overflow-y: auto;
        box-sizing: border-box;
    }
    .field .counter { font-size: 11px; color: #999; float: right; }

    .field-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .field-row .field { flex: 1; min-width: 140px; }

    fieldset { border: none; padding: 0; margin-bottom: 14px; }
    legend { font-size: 13px; color: #555; margin-bottom: 6px; padding: 0; }
    .pill { display: inline-flex; align-items: center; gap: 4px; background: #f2f2f2; border-radius: 20px; padding: 6px 14px; margin: 0 6px 6px 0; font-size: 13px; cursor: pointer; }
    .pill input { accent-color: #70c5d3; }

    .btn-submit { width: 100%; padding: 12px; font-size: 16px; border-radius: 8px; background: #70c5d3; color: #fff; border: none; cursor: pointer; }
    .cover-box {
        display: flex; align-items: center; justify-content: center;
        width: 100%; aspect-ratio: 16 / 9; max-height: 220px;
        background: #f2f2f2; border: 2px dashed #ccc; border-radius: 12px;
        position: relative; overflow: hidden; text-align: center; color: #888; font-size: 13px;
        cursor: grab; user-select: none;
    }
    .cover-box:active { cursor: grabbing; }
    .cover-box img { width: 100%; height: 100%; object-fit: cover; pointer-events: none; }
    .cover-hint {
        position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%);
        background: rgba(0,0,0,.55); color: #fff; font-size: 11px; padding: 3px 10px; border-radius: 20px;
    }
    .field-err { color: #d9534f; font-size: 12px; margin-top: 4px; }
    .cover-change-btn {
        position: absolute; top: 10px; right: 10px; z-index: 2;
        background: #fff; border: none; border-radius: 20px;
        padding: 6px 14px; font-size: 12px; cursor: pointer; color: #3a9fb0;
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
    }
    .cover-change-btn:hover { background: #f0f9fb; } </style>
@endsection

@section('content')
<div class="act-wrap">
    <a href="{{ route('activities.index') }}" class="back-link">← กลับ</a>

    <h1>สร้างกิจกรรม</h1>

    @include('activities._form', [
        'activity' => null,
        'action' => route('activities.store'),
        'method' => 'POST',
        'submitLabel' => 'สร้างกิจกรรม',
    ])
</div>
@endsection