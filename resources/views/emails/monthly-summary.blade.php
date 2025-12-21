<x-mail::message>
    # 📊 Tổng kết chi tiêu tháng {{ $month }}

    Xin chào **{{ $userName }}**,

    Đây là báo cáo tổng kết chi tiêu của bạn trong tháng {{ $month }}.

    ## Tổng quan

    **Tổng chi tiêu:** {{ $totalSpent }} VNĐ

    ## Chi tiêu theo danh mục

    @if(count($categories) > 0)
        <x-mail::table>
            | Danh mục | Số giao dịch | Tổng tiền |
            |:---------|:------------:|----------:|
            @foreach($categories as $category)
                | {{ $category['category'] }} | {{ $category['count'] }} | {{ number_format($category['total'], 0, ',', '.') }}
                VNĐ |
            @endforeach
        </x-mail::table>
    @else
        Bạn chưa có giao dịch nào trong tháng này.
    @endif

    <x-mail::button :url="url('/expenses')">
        Xem chi tiết
    </x-mail::button>

    **Gợi ý:**
    - So sánh với các tháng trước để phát hiện xu hướng chi tiêu
    - Xem xét điều chỉnh ngân sách cho tháng tiếp theo
    - Lập kế hoạch tiết kiệm dựa trên số liệu thực tế

    Trân trọng,<br>
    {{ config('app.name') }}
</x-mail::message>