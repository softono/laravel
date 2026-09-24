<div class="flex flex-wrap gap-4">
  <div class="w-full md:w-[calc(50%-0.5rem)]">
    <div class="card h-full">
      <div class="card-header">
        <div>
          <h5 class="card-title customer-active-chart">Active/Inactive users</h5>
        </div>
      </div>
      <div class="card-body">
        <canvas id="userCountChart"></canvas>
      </div>
    </div>
  </div>

  <div class="w-full md:w-[calc(50%-0.5rem)]">
  <div class="card h-full">
      <div class="card-header">
        <div>
          <h5 class="card-title mb-0 customer-chart">New Users</h5>
        </div>
            <select class="form-select w-auto"
             id="periodSelect" onchange="userChartDataUpdate(this.value)">
                <option value="day">Last 7 Days</option>
                <option value="month">Last 6 Months</option>
                <option value="year" selected>Last 12 Months</option>
            </select>
      </div>
      <div class="card-body">
        <canvas id="userChart"></canvas>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
    function updateChartData(chart,label,data) {
        chart.data.labels=label;
        chart.data.datasets[0].data=data;
        chart.update();
    }
    
    function userChartDataUpdate(selectedValue)
    {
         var postData = {
            '_token': CSRF_TOKEN, 
            'type': selectedValue  
        };
        $.ajax({
            url: "admin/site/get-chart-user" ,
            method: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                updateChartData(userChart,response.label,response.data);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
    
    var userCountChart =false;
    var userChart=false;
    
  documentReady(function() {
      app.loadScript('https://cdn.jsdelivr.net/npm/chart.js',function(){
          // userCountChart start 
         userCountChart = new Chart(document.getElementById('userCountChart').getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
              data: [<?= $activeUser ?? 0 ?>, <?= $deactiveUser ?? 0 ?>],
              backgroundColor: ['#9C94F4', '#ff6384'],
            }],
          },
    
          options: {
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  usePointStyle: true,
                },
              },
            },
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%', // Adjust the size of the hole in the center
            animation: {
              animateScale: true,
              animateRotate: true,
            },
    
            tooltips: {
              callbacks: {
                title: function(tooltipItem, data) {
                  var dataset = data.datasets[tooltipItem[0].datasetIndex];
                  return dataset.label;
                },
                label: function(tooltipItem, data) {
                  var dataset = data.datasets[tooltipItem.datasetIndex];
                  var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                    return previousValue + currentValue;
                  });
                  var currentValue = dataset.data[tooltipItem.index];
                  var percentage = ((currentValue / total) * 100).toFixed(2); // Rounded to 2 decimal places
    
                  return currentValue + " (" + percentage + "%)";
                },
              },
            },
          },
        });
        // userCountChart end 
        //userChart start
        userChart= new Chart(document.getElementById('userChart').getContext('2d'), {
            type: 'line',
            data:{
                labels:[],
                datasets: [{
                label: 'New Users',
                data:[],
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
              }]
            }
        });
        //userChart end
        userChartDataUpdate('year');
      });
  });
</script>
@endpush