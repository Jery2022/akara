import React, { useEffect, useRef } from 'react';

function ChartSection({ recettes, depenses }) {
  const chartRef = useRef(null);
  const chartInstance = useRef(null);

  // Effect to initialize and update the chart
  useEffect(() => {
    if (!chartRef.current || !window.Chart) {
      return;
    }

    // Destroy the previous chart instance if it exists
    if (chartInstance.current) {
      chartInstance.current.destroy();
    }

    const totalRecettes = recettes.reduce(
      (sum, r) => sum + parseFloat(r.total || 0),
      0
    );
    const totalDepenses = depenses.reduce(
      (sum, d) =>
        sum + parseFloat(d.quantity || 0) * parseFloat(d.price || 0),
      0
    );

    const ctx = chartRef.current.getContext('2d');
    chartInstance.current = new window.Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Recettes', 'Dépenses'],
        datasets: [
          {
            label: 'Montants (FCFA)',
            data: [totalRecettes, totalDepenses],
            backgroundColor: ['#10b981', '#ef4444'],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });

    // Cleanup function to destroy the chart on component unmount
    return () => {
      if (chartInstance.current) {
        chartInstance.current.destroy();
        chartInstance.current = null;
      }
    };
  }, [recettes, depenses]); // Re-run effect when data changes

  return (
    <div className="bg-white dark:bg-gray-800 p-4 rounded shadow" style={{ position: 'relative', height: '250px' }}>
      <canvas ref={chartRef}></canvas>
    </div>
  );
}

export default ChartSection;
