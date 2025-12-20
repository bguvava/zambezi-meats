<template>
  <div class="chart-container">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  labels: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  label: {
    type: String,
    default: 'Data'
  },
  color: {
    type: String,
    default: '#CF0D0F'
  },
  fillColor: {
    type: String,
    default: 'rgba(207, 13, 15, 0.1)'
  },
  isDarkMode: {
    type: Boolean,
    default: false
  },
  height: {
    type: Number,
    default: 300
  }
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [
    {
      label: props.label,
      data: props.data,
      backgroundColor: props.fillColor,
      borderColor: props.color,
      borderWidth: 3,
      fill: true,
      tension: 0.4, // Smooth curves
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: props.color,
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointHoverBackgroundColor: props.color,
      pointHoverBorderColor: '#fff',
      pointHoverBorderWidth: 2
    }
  ]
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: props.isDarkMode ? '#E5E7EB' : '#4D4B4C',
        font: {
          size: 12,
          weight: 600
        },
        padding: 15,
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      enabled: true,
      backgroundColor: props.isDarkMode ? '#1F2937' : '#fff',
      titleColor: props.isDarkMode ? '#fff' : '#4D4B4C',
      bodyColor: props.isDarkMode ? '#E5E7EB' : '#6F6F6F',
      borderColor: props.isDarkMode ? '#374151' : '#EFEFEF',
      borderWidth: 1,
      padding: 12,
      displayColors: true,
      callbacks: {
        label: function (context) {
          let label = context.dataset.label || ''
          if (label) {
            label += ': '
          }
          if (context.parsed.y !== null) {
            label += '$' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
          }
          return label
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: true,
        color: props.isDarkMode ? 'rgba(75, 85, 99, 0.3)' : 'rgba(239, 239, 239, 0.8)',
        drawBorder: false
      },
      ticks: {
        color: props.isDarkMode ? '#9CA3AF' : '#6F6F6F',
        font: {
          size: 11,
          weight: 500
        }
      }
    },
    y: {
      beginAtZero: true,
      grid: {
        display: true,
        color: props.isDarkMode ? 'rgba(75, 85, 99, 0.3)' : 'rgba(239, 239, 239, 0.8)',
        drawBorder: false
      },
      ticks: {
        color: props.isDarkMode ? '#9CA3AF' : '#6F6F6F',
        font: {
          size: 11,
          weight: 500
        },
        callback: function (value) {
          return '$' + value.toLocaleString('en-US')
        }
      }
    }
  },
  interaction: {
    mode: 'index',
    intersect: false
  }
}))
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}
</style>
