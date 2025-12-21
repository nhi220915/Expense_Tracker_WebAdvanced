<x-mail::message>
    # ⚠️ Cảnh báo Ngân sách

    Xin chào **{{ $userName }}**,

    Hệ thống phát hiện ngân sách của bạn đang sắp vượt quá giới hạn đã đặt.

    ## Thống kê chi tiêu hiện tại

    - **Ngân sách giới hạn:** {{ $budgetLimit }} VNĐ
    - **Đã chi tiêu:** {{ $currentSpent }} VNĐ
    - **Tỷ lệ sử dụng:** {{ $percentageUsed }}%
    - **Còn lại:** {{ $remaining }} VNĐ

    @if($percentageUsed >= 100)
        <x-mail::panel>
            🚨 **KHẨN CẤP:** Bạn đã vượt quá ngân sách!

            Vui lòng xem xét lại chi tiêu hoặc điều chỉnh ngân sách của bạn.
        </x-mail::panel>
    @elseif($percentageUsed >= 90)
        <x-mail::panel>
            ⚠️ **CẢNH BÁO:** Bạn đã sử dụng hơn 90% ngân sách!

            Hãy cân nhắc kỹ các khoản chi tiêu tiếp theo.
        </x-mail::panel>
    @elseif($percentageUsed >= 80)
        <x-mail::panel>
            💡 **LƯU Ý:** Bạn đã sử dụng hơn 80% ngân sách.

            Hãy theo dõi chi tiêu của bạn để tránh vượt quá giới hạn.
        </x-mail::panel>
    @endif

    <x-mail::button :url="url('/dashboard')">
        Xem Dashboard
    </x-mail::button>

    **Lời khuyên:**
    - Kiểm tra lại các khoản chi không cần thiết
    - Cân nhắc hoãn lại một số giao dịch
    - Cập nhật ngân sách nếu cần thiết

    Trân trọng,<br>
    {{ config('app.name') }}
</x-mail::message>