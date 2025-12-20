<template>
  <div class="chart-container">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  labels: {
    type: Array,
    required: true
  },
  datasets: {
    type: Array,
    required: true,
    // Each dataset should have: { label, data, backgroundColor, borderColor }
  },
  isDarkMode: {
    type: Boolean,
    default: false
  },
  height: {
    type: Number,
    default: 300
  },
  stacked: {
    type: Boolean,
    default: false
  }
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: props.datasets.map(dataset => ({
    ...dataset,
    borderWidth: 0,
    borderRadius: 6,
    barThickness: 'flex',
    maxBarThickness: 40
  }))
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
      stacked: props.stacked,
      grid: {
        display: false,
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
      stacked: props.stacked,
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
