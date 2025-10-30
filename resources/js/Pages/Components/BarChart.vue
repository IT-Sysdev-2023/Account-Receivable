<template>
    <div class="relative w-full h-full">
        <canvas ref="chartRef"></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

// Register all required Chart.js components
Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Title,
    Tooltip,
    Legend
);

// Custom rounded bar controller
class RoundedBarController extends BarController {
    draw() {
        const ctx = this.chart.ctx;
        const elements = this.getMeta().data;

        elements.forEach((element, index) => {
            const model = element;
            const { x, y, base, width } = model;
            const borderRadius = this.getDataset().borderRadius || 6;
            const height = base - y;

            ctx.save();
            ctx.beginPath();

            // Draw rounded top corners
            ctx.moveTo(x - width / 2, base);
            ctx.lineTo(x - width / 2, y + borderRadius);
            ctx.quadraticCurveTo(
                x - width / 2,
                y,
                x - width / 2 + borderRadius,
                y
            );
            ctx.lineTo(x + width / 2 - borderRadius, y);
            ctx.quadraticCurveTo(
                x + width / 2,
                y,
                x + width / 2,
                y + borderRadius
            );
            ctx.lineTo(x + width / 2, base);
            ctx.closePath();

            // Fill the bar
            ctx.fillStyle = model.options.backgroundColor;
            ctx.fill();

            // Draw border if needed
            if (model.options.borderWidth > 0) {
                ctx.strokeStyle = model.options.borderColor;
                ctx.lineWidth = model.options.borderWidth;
                ctx.stroke();
            }

            ctx.restore();
        });
    }
}

// Register our custom controller
Chart.register(RoundedBarController);

const props = defineProps({
    chartData: {
        type: Object,
        required: true,
    },
    chartOptions: {
        type: Object,
        default: () => ({}),
    },
});

const chartRef = ref(null);
let chartInstance = null;

onMounted(() => {
    if (chartRef.value) {
        chartInstance = new Chart(chartRef.value, {
            type: "bar",
            data: props.chartData,
            options: {
                ...props.chartOptions,
                datasets: {
                    bar: {
                        borderRadius: 6, // Default border radius
                    },
                },
            },
        });
    }
});

// Update chart when data changes
watch(
    () => props.chartData,
    (newData) => {
        if (chartInstance) {
            chartInstance.data = newData;
            chartInstance.update();
        }
    },
    { deep: true }
);

// Clean up chart instance
onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
});
</script>

<style scoped>
.chart-container {
    position: relative;
    height: 100%;
    width: 100%;
}
</style>
