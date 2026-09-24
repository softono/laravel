<div class="grid gap-6 md:grid-cols-2">
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title>Active / Inactive users</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            <canvas id="userCountChart"></canvas>
        </x-ui.card-content>
    </x-ui.card>

    <x-ui.card>
        <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
            <x-ui.card-title>New Users</x-ui.card-title>
            <x-ui.select id="periodSelect" class="w-auto">
                <option value="day">Last 7 Days</option>
                <option value="month">Last 6 Months</option>
                <option value="year" selected>Last 12 Months</option>
            </x-ui.select>
        </x-ui.card-header>
        <x-ui.card-content>
            <canvas id="userChart"></canvas>
        </x-ui.card-content>
    </x-ui.card>
</div>

@push('scripts')
    <script>
        documentReady(function() {
            app.loadScript('https://cdn.jsdelivr.net/npm/chart.js', function() {
                new Chart(document.getElementById('userCountChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Inactive'],
                        datasets: [{
                            data: [{{ $counts['active'] }}, {{ $counts['inactive'] }}],
                            backgroundColor: ['#9C94F4', '#ff6384'],
                        }],
                    },
                    options: {
                        plugins: {legend: {position: 'bottom', labels: {usePointStyle: true}}},
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                    },
                });

                var userChart = new Chart(document.getElementById('userChart'), {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{label: 'New Users', data: [], fill: false, borderColor: 'rgb(75, 192, 192)', tension: 0.1}],
                    },
                });

                function loadUserChart(type) {
                    app.ajaxPost('{{ route('admin/dashboard/chart-user') }}', {type: type}, function(response) {
                        if (!response.status) {
                            return;
                        }
                        userChart.data.labels = response.data.label;
                        userChart.data.datasets[0].data = response.data.data;
                        userChart.update();
                    });
                }

                $('#periodSelect').on('change', function() {
                    loadUserChart(this.value);
                });
                loadUserChart('year');
            });
        });
    </script>
@endpush
