<div class="dashboard-block"> 
    <h3>💰 Current Budget Progress</h3>
    <ul class="budget-progress-list" id="budgetProgressList">
        @foreach($budgetProgress ?? [] as $budget)
            @php
                $spent = $budget['spent'] ?? 0;
                $limit = $budget['limit'] ?? 0;
                $percentage = $limit > 0 ? min(($spent / $limit) * 100, 100) : 0;
                $color = $percentage >= 90 ? 'red' : 'green';
                $status = $percentage > 100 ? '(Exceeded!)' : ($percentage >= 90 ? '(Close to limit!)' : '');
            @endphp
            <li class="budget-item">
                <h4>
                    <span>{{ $budget['category'] ?? '' }}</span>
                    <span style="color: {{ $color === 'red' ? '#e74c3c' : '#6c7a89' }};">
                        ${{ number_format($spent, 2) }} / ${{ number_format($limit, 2) }} {{ $status }}
                    </span>
                </h4>
                <div class="progress-bar-container">
                    <div class="progress-bar {{ $color }}" style="width: {{ $percentage }}%;"></div>
                </div>
            </li>
        @endforeach
    </ul>
</div>