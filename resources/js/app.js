// import "./bootstrap";
import Chart from "chart.js/auto";
import ChartDataLabels from "chartjs-plugin-datalabels";
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import state management modules
import { sidebarState } from "./sidebar-state";
import { themeState } from "./theme-state";

// Make state management functions available globally for Alpine.js
window.sidebarState = sidebarState;
window.themeState = themeState;

Chart.register(ChartDataLabels);

window.Chart = Chart;
window.ChartDataLabels = ChartDataLabels;
