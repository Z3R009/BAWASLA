// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#292b2c';

// Area Chart Example
var ctx = document.getElementById("myAreaChart");
var myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"], // Month labels
        datasets: [{
            label: "Sessions",
            lineTension: 0.3,
            backgroundColor: "rgba(2,117,216,0.2)",
            borderColor: "rgba(2,117,216,1)",
            pointRadius: 5,
            pointBackgroundColor: "rgba(2,117,216,1)",
            pointBorderColor: "rgba(255,255,255,0.8)",
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "rgba(2,117,216,1)",
            pointHitRadius: 50,
            pointBorderWidth: 2,
            data: [20000, 30162, 26263, 18394, 18287, 28682, 31274, 33259, 25849, 24159, 32651, 31984], // Sample data for each month
        }],
    },
    options: {
        scales: {
            xAxes: [{
                gridLines: {
                    display: false
                },
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 12, // Limit the number of ticks to the number of months
                }
            }],
            yAxes: [{
                ticks: {
                    min: 0,
                    max: 40000,
                    maxTicksLimit: 5
                },
                gridLines: {
                    color: "rgba(0, 0, 0, .125)",
                }
            }],
        },
        legend: {
            display: false
        }
    }
});