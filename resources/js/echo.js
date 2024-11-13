import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 4434,
    forceTLS: false, // Thay đổi forceTLS thành false nếu không dùng SSL
    enabledTransports: ['ws', 'wss'], // Sử dụng 'ws' thay vì 'wss'
    authEndpoint: '/broadcasting/auth', // Thêm authEndpoint để xác thực cho Private và Presence Channels
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`, // Đảm bảo token từ Passport được truyền vào
        }
    }
});

// Lắng nghe các sự kiện kết nối
// window.Echo.connector.pusher.connection.bind('connected', () => {
//     console.log('WebSocket connected successfully!');
// });

// window.Echo.connector.pusher.connection.bind('disconnected', () => {
//     console.log('WebSocket disconnected.');
// });

// window.Echo.connector.pusher.connection.bind('error', (error) => {
//     console.error('WebSocket connection error:', error);
// });