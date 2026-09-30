<table>
    <tr><th>ชื่อ</th><th>อีเมล</th><th>สิทธิ์</th><th>แต้ม</th><th>สมัครเมื่อ</th></tr>
    @forelse ($users as $u)
        <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td><span class="tag {{ $u->role === 'admin' ? 'admin' : '' }}">{{ $u->role }}</span></td>
            <td>{{ number_format($u->totalPoints()) }}</td>
            <td>{{ $u->created_at?->format('d/m/Y') }}</td>
        </tr>
    @empty
        <tr><td colspan="5">ไม่พบผู้ใช้</td></tr>
    @endforelse
</table>

 <div class="pager">
    <span>หน้า {{ $users->currentPage() }} / {{ $users->lastPage() }} (ทั้งหมด {{ $users->total() }} คน)</span>
    <span>
        @if ($users->previousPageUrl()) <a href="{{ $users->previousPageUrl() }}">← ก่อนหน้า</a> @endif
        @if ($users->nextPageUrl()) <a href="{{ $users->nextPageUrl() }}">ถัดไป →</a> @endif
    </span>
</div>