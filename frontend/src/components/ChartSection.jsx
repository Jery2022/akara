import React, { useEffect, useRef } from 'react';

// Chart Section Component (sécurisation du chargement Chart.js)
// import Chart.js from CDN

function ChartSection({ payments }) {
  const chartRef = useRef(null);
  const chartInstance = useRef(null);

  useEffect(() => {
    let isMounted = true;
    async function loadChart() {
      if (!window.Chart) {
        if (!document.getElementById('chartjs-script')) {
          await new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.id = 'chartjs-script';
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.async = true;
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
          });
        } else {
          await new Promise((resolve) => {
            const interval = setInterval(() => {
              if (window.Chart) {
                clearInterval(interval);
                resolve();
              }
            }, 50);
          });
        }
      }

      if (isMounted && window.Chart && chartRef.current) {
        if (chartInstance.current) {
          chartInstance.current.destroy();
        }

        const ctx = chartRef.current.getContext('2d');
        chartInstance.current = new window.Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Recettes', 'Dépenses'],
            datasets: [
              {
                label: 'Montants (FCFA)',
                data: [
                  payments
                    .filter((p) => p.type === 'income')
                    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0),
                  payments
                    .filter((p) => p.type === 'expense')
                    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0),
                ],
                backgroundColor: ['#10b981', '#ef4444'],
              },
            ],
          },
          options: { responsive: true },
        });
      }
    }
    loadChart();
    return () => {
      isMounted = false;
      if (chartInstance.current) {
        chartInstance.current.destroy();
        chartInstance.current = null;
      }
    };
  }, [payments]);

  return (
    <div className="bg-white dark:bg-gray-800 p-4 rounded shadow">
      <canvas ref={chartRef} width="400" height="200"></canvas>
    </div>
  );
}
export default ChartSection;
