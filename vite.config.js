import { defineConfig } from 'vite'; // <--- Dòng này cực kỳ quan trọng
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/components/budget-modals.js',
                'resources/js/pages/expense-crud.js',
            ],
            refresh: true,
        }),
    ],
});
