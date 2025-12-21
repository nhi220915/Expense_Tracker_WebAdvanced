<x-mail::message>
    # 💡 Nhắc nhở ghi chi tiêu

    Xin chào **{{ $userName }}**,

    Chúng tôi nhận thấy bạn chưa ghi lại chi tiêu nào trong **{{ $daysSince }} ngày** qua.

    <x-mail::panel>
        📝 **Ghi nhớ:**

        Việc ghi chi tiêu thường xuyên giúp bạn:
        - Theo dõi ngân sách chính xác hơn
        - Phát hiện các khoản chi không cần thiết
        - Lập kế hoạch tài chính hiệu quả
    </x-mail::panel>

    @if($daysSince >= 7)
        ⚠️ Đã hơn **1 tuần** kể từ lần ghi chi tiêu cuối cùng. Hãy cập nhật để đảm bảo dữ liệu chính xác!
    @endif

    <x-mail::button :url="url('/expenses')">
        Ghi chi tiêu ngay
    </x-mail::button>

    **Lời khuyên nhanh:**
    - Dành 2-3 phút mỗi ngày để ghi chi tiêu
    - Sử dụng ứng dụng mobile để ghi nhanh khi di chuyển
    - Lưu hóa đơn để tiện tra cứu sau này

    Trân trọng,<br>
    {{ config('app.name') }}
</x-mail::message>