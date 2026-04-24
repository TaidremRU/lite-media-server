<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, user-scalable=yes">
    <title>Lite Media Server</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #121212 0%, #1a1a2e 100%);
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        .player-card {
            max-width: 1100px;
            width: 100%;
            background: rgba(30, 30, 40, 0.75);
            backdrop-filter: blur(2px);
            border-radius: 2rem;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .video-wrapper {
            background: #000;
            position: relative;
            width: 100%;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        video {
            width: 100%;
            height: auto;
            display: block;
            background: #000;
            outline: none;
        }

        .video-placeholder {
            background: #0a0a12;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            text-align: center;
            color: #aaa;
            gap: 1rem;
            width: 100%;
        }

        .video-placeholder-icon {
            font-size: 4rem;
            opacity: 0.5;
        }

        .video-placeholder-text {
            font-size: 1rem;
            max-width: 80%;
        }

        .unsupported-fallback {
            background: #1e1e2a;
            padding: 2rem;
            text-align: center;
            color: #ffcc99;
            border-radius: 1rem;
            margin: 1rem;
        }

        .video-meta {
            padding: 1rem 1.5rem;
            background: rgba(20, 20, 28, 0.9);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .current-video {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: wrap;
            font-weight: 500;
            word-break: break-word;
            flex: 2;
        }

        .current-label {
            background: #3a3a4e;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
            font-size: 0.75rem;
        }

        .video-name {
            font-family: monospace;
            font-size: 0.85rem;
            color: #b9fbc0;
            background: #1e2a2a;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
            overflow-x: auto;
            white-space: nowrap;
            max-width: 100%;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .toggle-library-btn, .download-current-btn, .toggle-torrent-btn {
            background: #2c6e9e;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .toggle-library-btn {
            background: #4a4a62;
        }

        .toggle-torrent-btn {
            background: #3a6e4e;
        }

        .toggle-library-btn:hover, .download-current-btn:hover, .toggle-torrent-btn:hover {
            background: #1f4f73;
            transform: scale(0.97);
        }

        .torrent-panel {
            background: rgba(25, 30, 40, 0.9);
            margin: 12px 1.5rem 0 1.5rem;
            border-radius: 1.5rem;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: max-height 0.3s ease, opacity 0.3s ease, margin 0.3s, padding 0.3s;
            overflow: hidden;
        }

        .torrent-panel.hidden-torrent {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            margin-bottom: 0;
            opacity: 0;
            pointer-events: none;
        }

        .disk-space {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
            color: #ddd;
        }

        .add-torrent-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 1rem;
            align-items: center;
        }

        .add-torrent-form input {
            flex: 3;
            background: #1e1e2a;
            border: 1px solid #3a3a4e;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            color: white;
            font-size: 0.85rem;
            outline: none;
        }

        .add-torrent-form input:focus {
            border-color: #5c9eff;
        }

        .file-input-label {
            background: #2c6e9e;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            color: white;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .file-input-label:hover {
            background: #1f4f73;
        }

        .torrent-list {
            max-height: 300px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .torrent-item {
            background: rgba(30, 30, 45, 0.7);
            border-radius: 1rem;
            padding: 0.7rem 1rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            border-left: 3px solid #5c9eff;
        }

        .torrent-name {
            flex: 2;
            font-size: 0.85rem;
            font-weight: 500;
            word-break: break-word;
            color: #e0e0e0;
        }

        .torrent-progress {
            flex: 1;
            min-width: 120px;
        }

        .progress-bar-bg {
            background: #2a2a3a;
            border-radius: 1rem;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: #5c9eff;
            width: 0%;
            height: 100%;
            border-radius: 1rem;
            transition: width 0.2s;
        }

        .progress-text {
            font-size: 0.7rem;
            margin-top: 4px;
            text-align: center;
            color: #aaa;
        }

        .torrent-actions {
            display: flex;
            gap: 6px;
        }

        .torrent-delete {
            background: #aa5544;
            border: none;
            border-radius: 2rem;
            padding: 0.3rem 0.8rem;
            color: white;
            font-size: 0.7rem;
            cursor: pointer;
        }

        .torrent-delete:hover {
            background: #cc6644;
        }

        .status-message {
            text-align: center;
            padding: 2rem;
            color: #aaa;
        }

        .error-message {
            color: #ffaa99;
            background: rgba(200, 60, 40, 0.2);
            border-left: 3px solid #ff8866;
            padding: 0.8rem;
            border-radius: 1rem;
            margin-top: 12px;
        }

        .playlist-section {
            padding: 1.2rem 1.5rem 1.5rem;
            transition: max-height 0.4s ease, opacity 0.3s ease, padding 0.3s;
            overflow: hidden;
        }

        .playlist-section.hidden-library {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            opacity: 0;
            pointer-events: none;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            gap: 12px;
        }

        h3 {
            font-size: 1.35rem;
            font-weight: 600;
            background: linear-gradient(135deg, #e9e9ff, #b0c4ff);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .refresh-btn, .lang-btn, .torrent-refresh {
            background: #2c2c3a;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: 0.2s;
        }

        .refresh-btn:hover, .lang-btn:hover, .torrent-refresh:hover {
            background: #3e3e52;
            color: white;
        }

        .video-tree {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .tree-folder {
            margin-top: 0.5rem;
        }

        .folder-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.6rem 0.8rem;
            background: rgba(40, 40, 55, 0.6);
            border-radius: 1rem;
            cursor: pointer;
            font-weight: 600;
            color: #ccddee;
            transition: 0.2s;
            user-select: none;
        }

        .folder-header:hover {
            background: rgba(60, 60, 80, 0.8);
        }

        .folder-icon {
            font-size: 1.2rem;
        }

        .folder-name {
            flex: 1;
            font-size: 0.9rem;
        }

        .folder-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .delete-btn {
            background: rgba(200, 60, 50, 0.8);
            border: none;
            border-radius: 2rem;
            padding: 0.2rem 0.6rem;
            color: white;
            font-size: 0.7rem;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .delete-btn:hover {
            background: #cc4433;
            transform: scale(1.05);
        }

        .toggle-icon {
            font-size: 0.8rem;
            transition: transform 0.2s;
        }

        .folder-content {
            margin-left: 1.2rem;
            padding-left: 0.5rem;
            border-left: 2px solid rgba(100, 150, 200, 0.3);
            overflow: hidden;
        }

        .folder-content.collapsed {
            display: none;
        }

        .video-item {
            background: rgba(25, 25, 35, 0.7);
            border-radius: 1rem;
            padding: 0.8rem 1rem;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 0.4rem;
        }

        .video-item:hover {
            background: #2d2d40;
            transform: translateX(5px);
        }

        .video-item.active {
            background: linear-gradient(95deg, #2a3a5a, #1f2b3f);
            border-left: 4px solid #5c9eff;
        }

        .video-icon {
            font-size: 1.3rem;
        }

        .video-filename {
            font-size: 0.85rem;
            font-weight: 500;
            color: #f0f0f0;
            flex: 1;
            font-family: monospace;
            word-break: break-word;
        }

        .video-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .format-badge {
            background: #ff8c5a;
            font-size: 0.65rem;
            padding: 0.2rem 0.6rem;
            border-radius: 40px;
            color: #1a1a2a;
            font-weight: bold;
            text-transform: uppercase;
        }

        .timeline-icon {
            background: #5c9eff;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 40px;
            color: #1a1a2a;
            font-weight: bold;
            cursor: help;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .footer {
            padding: 0.8rem 1.5rem;
            text-align: center;
            font-size: 0.7rem;
            color: #888;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(20, 20, 28, 0.8);
        }

        .hidden {
            display: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.2);
            border-top: 2px solid #5c9eff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 640px) {
            body { padding: 0.8rem; }
            .video-item { padding: 0.7rem 0.9rem; }
            .video-filename { font-size: 0.75rem; }
            .folder-header { padding: 0.5rem 0.7rem; }
            .folder-content { margin-left: 0.8rem; }
            .action-buttons { width: 100%; justify-content: flex-end; }
            .section-header { flex-wrap: wrap; }
            .torrent-panel { margin: 12px 0.8rem 0 0.8rem; }
            .add-torrent-form input { flex: 1; min-width: 150px; }
        }
    </style>
</head>
<body>

<div class="player-card">
    <div id="videoArea" class="video-wrapper">
        <div id="videoPlaceholder" class="video-placeholder">
            <div class="video-placeholder-icon">🎬</div>
            <div class="video-placeholder-text" data-i18n="placeholder_text">Выберите видео из списка ниже</div>
        </div>
        <video id="mainVideoPlayer" controls playsinline webkit-playsinline="true" preload="metadata" class="hidden"></video>
        <div id="unsupportedPlaceholder" class="unsupported-fallback hidden"></div>
    </div>

    <div class="video-meta">
        <div class="current-video">
            <span class="current-label" data-i18n="now_playing">▶ Сейчас играет</span>
            <span id="currentVideoName" class="video-name">— не выбрано —</span>
        </div>
        <div class="action-buttons">
            <button id="toggleTorrentBtn" class="toggle-torrent-btn">📥 Torrents</button>
            <button id="toggleLibraryBtn" class="toggle-library-btn">☰ Список</button>
            <button id="downloadCurrentBtn" class="download-current-btn hidden">📥 Скачать</button>
        </div>
        <div id="playbackStatus" style="font-size: 0.7rem; opacity: 0.7;">✓ готов</div>
    </div>

    <div id="torrentPanel" class="torrent-panel hidden-torrent">
        <div class="disk-space" id="diskSpaceInfo">
            <span data-i18n="disk_loading">Загрузка информации о диске...</span>
        </div>
        <div class="add-torrent-form">
            <input type="text" id="magnetInput" placeholder="magnet:?xt=urn:btih:..." data-i18n-placeholder="magnet_placeholder">
            <button id="addMagnetBtn" class="refresh-btn" data-i18n="add_magnet">➕ Добавить magnet</button>
            <label class="file-input-label">
                📁 <span data-i18n="upload_torrent">Выбрать .torrent</span>
                <input type="file" id="torrentFileInput" accept=".torrent" style="display: none;">
            </label>
            <button id="refreshTorrentsBtn" class="torrent-refresh" data-i18n="refresh_torrents">🔄 Обновить</button>
        </div>
        <div id="torrentListContainer" class="torrent-list">
            <div class="status-message"><span class="spinner"></span> <span data-i18n="loading_torrents">Загрузка торрентов...</span></div>
        </div>
    </div>

    <div id="playlistSection" class="playlist-section">
        <div class="section-header">
            <h3 data-i18n="library_title">📼 Библиотека видео</h3>
            <div style="display: flex; gap: 8px;">
                <button id="langBtn" class="lang-btn">🌐 EN</button>
                <button id="refreshListBtn" class="refresh-btn">🔄 Обновить список</button>
            </div>
        </div>
        <div id="videoTreeContainer" class="video-tree">
            <div class="status-message"><span class="spinner"></span> <span data-i18n="loading_videos">Загрузка видео...</span></div>
        </div>
        <div id="apiErrorMsg" class="error-message hidden"></div>
    </div>

    <div class="footer">
        <span data-i18n="version">Lite Media Server — версия 0.2</span>
    </div>
</div>

<script>
    (function() {
        // --- Локализация (версия 0.2) ---
        const translations = {
            ru: {
                placeholder_text: "Выберите видео из списка ниже",
                now_playing: "▶ Сейчас играет",
                library_title: "📼 Библиотека видео",
                loading_videos: "Загрузка видео...",
                no_videos: "📭 Видео не найдены",
                ready: "✓ готов",
                btn_refresh: "🔄 Обновить список",
                btn_toggle_show: "☰ Список",
                btn_toggle_hide: "📋 Показать",
                btn_download: "📥 Скачать",
                btn_toggle_torrent_show: "📥 Torrents",
                btn_toggle_torrent_hide: "📥 Скрыть",
                status_ready: "✓ готов",
                status_loading: "Загрузка: {name}",
                status_playing: "Воспроизводится: {name}",
                status_paused: "Пауза",
                status_ended: "Воспроизведение завершено",
                status_buffering: "Буферизация...",
                status_autoplay_blocked: "Автовоспроизведение заблокировано. Нажмите play.",
                status_codec_error: "Кодек не поддерживается. Попробуйте скачать.",
                status_network_error: "Ошибка загрузки видео. Файл недоступен или повреждён.",
                status_404: "Файл не найден (404). Проверьте путь.",
                status_decode_error: "Ошибка декодирования. Возможно, файл повреждён.",
                api_invalid_format: "Неверный формат: ожидается {{ field }}",
                api_empty_list: "Список видео пуст.",
                api_network_error: "Не удалось загрузить список. Проверьте /listing.php",
                unsupported_title: "Формат {ext} не поддерживается для встроенного воспроизведения",
                unsupported_desc: "Браузеры не могут проиграть файлы .{ext} внутри страницы. Скачайте видео и откройте любым плеером на устройстве.",
                unsupported_download: "📥 Скачать ({ext})",
                unsupported_only_download: "{name} ({ext} — только скачивание)",
                msg_select_video: "— не выбрано —",
                msg_file_not_found: "Файл не найден",
                msg_download_hint: "Скачать файл",
                version: "Lite Media Server — версия 0.2",
                delete_file_confirm: "Вы действительно хотите удалить файл \"{name}\"? Это действие необратимо.",
                delete_folder_confirm: "Вы действительно хотите удалить папку \"{name}\" со всем содержимым? Это действие необратимо.",
                delete_success: "Удалено успешно",
                delete_error: "Ошибка при удалении: {error}",
                btn_delete: "🗑️",
                disk_loading: "Загрузка информации о диске...",
                disk_free: "Свободно: {free} / {total}",
                magnet_placeholder: "magnet:?xt=urn:btih:...",
                add_magnet: "➕ Добавить magnet",
                upload_torrent: "📁 Выбрать .torrent",
                refresh_torrents: "🔄 Обновить",
                loading_torrents: "Загрузка торрентов...",
                no_torrents: "Нет активных загрузок",
                torrent_progress: "Прогресс: {percent}%",
                torrent_delete_confirm: "Удалить торрент \"{name}\"?",
                torrent_add_success: "Торрент добавлен",
                torrent_add_error: "Ошибка добавления: {error}",
                torrent_delete_success: "Торрент удалён",
                torrent_delete_error: "Ошибка удаления: {error}"
            },
            en: {
                placeholder_text: "Select a video from the list below",
                now_playing: "▶ Now playing",
                library_title: "📼 Video library",
                loading_videos: "Loading videos...",
                no_videos: "📭 No videos found",
                ready: "✓ ready",
                btn_refresh: "🔄 Refresh list",
                btn_toggle_show: "☰ List",
                btn_toggle_hide: "📋 Show",
                btn_download: "📥 Download",
                btn_toggle_torrent_show: "📥 Torrents",
                btn_toggle_torrent_hide: "📥 Hide",
                status_ready: "✓ ready",
                status_loading: "Loading: {name}",
                status_playing: "Playing: {name}",
                status_paused: "Paused",
                status_ended: "Playback finished",
                status_buffering: "Buffering...",
                status_autoplay_blocked: "Autoplay blocked. Press play.",
                status_codec_error: "Codec not supported. Try downloading.",
                status_network_error: "Video load error. File unavailable or corrupted.",
                status_404: "File not found (404). Check the path.",
                status_decode_error: "Decoding error. File may be corrupted.",
                api_invalid_format: "Invalid format: expected {{ field }}",
                api_empty_list: "Video list is empty.",
                api_network_error: "Failed to load list. Check /listing.php",
                unsupported_title: "Format {ext} is not supported for embedded playback",
                unsupported_desc: "Browsers cannot play .{ext} files inside the page. Download the video and open it with any player on your device.",
                unsupported_download: "📥 Download ({ext})",
                unsupported_only_download: "{name} ({ext} — download only)",
                msg_select_video: "— not selected —",
                msg_file_not_found: "File not found",
                msg_download_hint: "Download file",
                version: "Lite Media Server — version 0.2",
                delete_file_confirm: "Are you sure you want to delete the file \"{name}\"? This action is irreversible.",
                delete_folder_confirm: "Are you sure you want to delete the folder \"{name}\" with all its contents? This action is irreversible.",
                delete_success: "Deleted successfully",
                delete_error: "Delete error: {error}",
                btn_delete: "🗑️",
                disk_loading: "Loading disk info...",
                disk_free: "Free: {free} / {total}",
                magnet_placeholder: "magnet:?xt=urn:btih:...",
                add_magnet: "➕ Add magnet",
                upload_torrent: "📁 Select .torrent",
                refresh_torrents: "🔄 Refresh",
                loading_torrents: "Loading torrents...",
                no_torrents: "No active downloads",
                torrent_progress: "Progress: {percent}%",
                torrent_delete_confirm: "Delete torrent \"{name}\"?",
                torrent_add_success: "Torrent added",
                torrent_add_error: "Add error: {error}",
                torrent_delete_success: "Torrent deleted",
                torrent_delete_error: "Delete error: {error}"
            }
        };

        let currentLang = 'ru';

        function t(key, params = {}) {
            let str = translations[currentLang][key] || translations['ru'][key] || key;
            for (const [k, v] of Object.entries(params)) {
                str = str.replace(new RegExp(`\\{${k}\\}`, 'g'), v);
            }
            return str;
        }

        function detectLanguage() {
            const browserLang = navigator.language || navigator.userLanguage;
            if (browserLang && browserLang.toLowerCase().startsWith('ru')) {
                return 'ru';
            }
            return 'en';
        }

        // --- DOM элементы ---
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        const videoPlayer = document.getElementById('mainVideoPlayer');
        const unsupportedPlaceholder = document.getElementById('unsupportedPlaceholder');
        const videoTreeContainer = document.getElementById('videoTreeContainer');
        const currentVideoNameSpan = document.getElementById('currentVideoName');
        const refreshBtn = document.getElementById('refreshListBtn');
        const apiErrorDiv = document.getElementById('apiErrorMsg');
        const playbackStatusSpan = document.getElementById('playbackStatus');
        const downloadCurrentBtn = document.getElementById('downloadCurrentBtn');
        const playlistSection = document.getElementById('playlistSection');
        const toggleLibraryBtn = document.getElementById('toggleLibraryBtn');
        const langBtn = document.getElementById('langBtn');
        const toggleTorrentBtn = document.getElementById('toggleTorrentBtn');
        const torrentPanel = document.getElementById('torrentPanel');
        const diskSpaceInfo = document.getElementById('diskSpaceInfo');
        const magnetInput = document.getElementById('magnetInput');
        const addMagnetBtn = document.getElementById('addMagnetBtn');
        const torrentFileInput = document.getElementById('torrentFileInput');
        const refreshTorrentsBtn = document.getElementById('refreshTorrentsBtn');
        const torrentListContainer = document.getElementById('torrentListContainer');

        // --- Настройки ---
        const ROOT_FOLDER_TO_SKIP = "video";
        const SUPPORTED_EXTENSIONS = ['mp4', 'm4v', 'mov', 'webm', 'ogg', 'ogv'];
        const IGNORED_EXTENSIONS = ['tmp', 'png']; // расширения, которые игнорируются и не показываются
        
        function isFormatSupported(filePath) {
            const ext = filePath.split('.').pop().toLowerCase();
            return SUPPORTED_EXTENSIONS.includes(ext);
        }

        function getFileExtension(filePath) {
            return filePath.split('.').pop().toLowerCase();
        }
        
        function isIgnoredExtension(filePath) {
            const ext = getFileExtension(filePath);
            return IGNORED_EXTENSIONS.includes(ext);
        }

        // --- состояние ---
        let videoItemsArray = [];
        let currentVideoPath = null;
        let currentDisplayName = "";
        let torrentRefreshInterval = null;
        
        // --- Timeline state ---
        let timelinesMap = new Map(); // name -> time (seconds)
        let timelineSaveInterval = null;
        let currentTimelineVideoName = null;
        
        // --- Timeline API functions ---
        async function saveTimelineToServer(videoName, time) {
            if (!videoName) return;
            try {
                const url = `/timeline.php?add&name=${encodeURIComponent(videoName)}&time=${Math.floor(time)}`;
                await fetch(url, { method: 'GET', cache: 'no-cache' });
            } catch (err) {
                console.warn('Save timeline error:', err);
            }
        }
        
        async function getTimelineFromServer(videoName) {
            if (!videoName) return 0;
            try {
                const resp = await fetch(`/timeline.php?get&name=${encodeURIComponent(videoName)}`, { cache: 'no-cache' });
                if (!resp.ok) return 0;
                const data = await resp.json();
                return data.time ? Number(data.time) : 0;
            } catch (err) {
                console.warn('Get timeline error:', err);
                return 0;
            }
        }
        
        async function getAllTimelines() {
            try {
                const resp = await fetch('/timeline.php?getall', { cache: 'no-cache' });
                if (!resp.ok) return [];
                const data = await resp.json();
                if (Array.isArray(data)) return data;
                return [];
            } catch (err) {
                console.warn('Get all timelines error:', err);
                return [];
            }
        }
        
        async function deleteTimelineFromServer(videoName) {
            if (!videoName) return;
            try {
                await fetch(`/timeline.php?del&name=${encodeURIComponent(videoName)}`, { method: 'GET', cache: 'no-cache' });
            } catch (err) {
                console.warn('Delete timeline error:', err);
            }
        }
        
        async function refreshTimelinesMap() {
            const all = await getAllTimelines();
            const newMap = new Map();
            for (const item of all) {
                if (item.name && typeof item.time === 'number') {
                    newMap.set(item.name, item.time);
                }
            }
            timelinesMap = newMap;
        }
        
        // --- Timeline auto-save logic ---
        function startTimelineSaving(videoName) {
            stopTimelineSaving();
            if (!videoName) return;
            currentTimelineVideoName = videoName;
            timelineSaveInterval = setInterval(() => {
                if (currentTimelineVideoName === videoName && videoPlayer && !videoPlayer.paused && !videoPlayer.ended && videoPlayer.readyState >= 2) {
                    const currentTime = videoPlayer.currentTime;
                    if (currentTime > 0 && isFinite(currentTime)) {
                        saveTimelineToServer(videoName, currentTime);
                    }
                }
            }, 10000); // каждые 10 секунд
        }
        
        function stopTimelineSaving() {
            if (timelineSaveInterval) {
                clearInterval(timelineSaveInterval);
                timelineSaveInterval = null;
            }
            currentTimelineVideoName = null;
        }
        
        // Сохраняем перед закрытием страницы
        window.addEventListener('beforeunload', () => {
            if (currentTimelineVideoName && videoPlayer && !videoPlayer.paused && !videoPlayer.ended && videoPlayer.currentTime > 0) {
                saveTimelineToServer(currentTimelineVideoName, videoPlayer.currentTime);
            }
        });

        // --- Torrent функции ---
        async function fetchDiskInfo() {
            try {
                const resp = await fetch('/disk.php');
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const data = await resp.json();
                if (data && data.free_human && data.total_human) {
                    diskSpaceInfo.innerHTML = `<span>💾 ${t('disk_free', { free: data.free_human, total: data.total_human })}</span>`;
                } else {
                    diskSpaceInfo.innerHTML = `<span>⚠️ ${t('disk_loading')}</span>`;
                }
            } catch (err) {
                console.error('Disk info error:', err);
                diskSpaceInfo.innerHTML = `<span>⚠️ ${t('disk_loading')}</span>`;
            }
        }

        async function fetchTorrents() {
            if (torrentPanel.classList.contains('hidden-torrent')) return;
            try {
                const resp = await fetch('/torrent/list.php');
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const data = await resp.json();
                let torrents = [];
                if (Array.isArray(data)) {
                    torrents = data;
                } else if (data && data.arguments && Array.isArray(data.arguments.torrents)) {
                    torrents = data.arguments.torrents;
                } else if (data && data.torrents) {
                    torrents = data.torrents;
                }
                renderTorrents(torrents);
            } catch (err) {
                console.error('Torrent list error:', err);
                torrentListContainer.innerHTML = `<div class="status-message">⚠️ ${t('api_network_error')}</div>`;
            }
        }

        function renderTorrents(torrents) {
            if (!torrents || !torrents.length) {
                torrentListContainer.innerHTML = `<div class="status-message">${t('no_torrents')}</div>`;
                return;
            }
            const container = document.createElement('div');
            container.className = 'torrent-list';
            for (const tor of torrents) {
                const item = document.createElement('div');
                item.className = 'torrent-item';
                const nameDiv = document.createElement('div');
                nameDiv.className = 'torrent-name';
                nameDiv.textContent = tor.name || 'Unnamed';
                const progressDiv = document.createElement('div');
                progressDiv.className = 'torrent-progress';
                const percent = tor.percentDone ? (tor.percentDone * 100).toFixed(1) : (tor.progress || 0);
                progressDiv.innerHTML = `
                    <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: ${percent}%;"></div></div>
                    <div class="progress-text">${t('torrent_progress', { percent })}</div>
                `;
                const actionsDiv = document.createElement('div');
                actionsDiv.className = 'torrent-actions';
                const delBtn = document.createElement('button');
                delBtn.className = 'torrent-delete';
                delBtn.textContent = t('btn_delete');
                delBtn.addEventListener('click', () => deleteTorrent(tor.id, tor.name));
                actionsDiv.appendChild(delBtn);
                item.appendChild(nameDiv);
                item.appendChild(progressDiv);
                item.appendChild(actionsDiv);
                container.appendChild(item);
            }
            torrentListContainer.innerHTML = '';
            torrentListContainer.appendChild(container);
        }

        async function addTorrentMagnet(magnetLink) {
            if (!magnetLink.trim()) return;
            try {
                const resp = await fetch(`/torrent/add.php?magnet=${encodeURIComponent(magnetLink)}`);
                if (!resp.ok) throw new Error(await resp.text());
                showStatusMessage(t('torrent_add_success'), false);
                magnetInput.value = '';
                await fetchTorrents();
                await fetchDiskInfo();
            } catch (err) {
                showStatusMessage(t('torrent_add_error', { error: err.message }), true);
            }
        }

        async function addTorrentFile(file) {
            const formData = new FormData();
            formData.append('torrent', file);
            try {
                const resp = await fetch('/torrent/add.php', { method: 'POST', body: formData });
                if (!resp.ok) throw new Error(await resp.text());
                showStatusMessage(t('torrent_add_success'), false);
                await fetchTorrents();
                await fetchDiskInfo();
            } catch (err) {
                showStatusMessage(t('torrent_add_error', { error: err.message }), true);
            }
        }

        async function deleteTorrent(id, name) {
            if (!confirm(t('torrent_delete_confirm', { name }))) return;
            try {
                const resp = await fetch(`/torrent/remove.php?id=${encodeURIComponent(id)}`);
                if (!resp.ok) throw new Error(await resp.text());
                showStatusMessage(t('torrent_delete_success'), false);
                await fetchTorrents();
                await fetchDiskInfo();
            } catch (err) {
                showStatusMessage(t('torrent_delete_error', { error: err.message }), true);
            }
        }

        // --- Управление интервалом обновления торрентов ---
        function startTorrentRefresh() {
            if (torrentRefreshInterval) clearInterval(torrentRefreshInterval);
            torrentRefreshInterval = setInterval(() => {
                if (document.visibilityState === 'visible' && !torrentPanel.classList.contains('hidden-torrent')) {
                    fetchTorrents();
                    fetchDiskInfo();
                }
            }, 5000);
        }

        function stopTorrentRefresh() {
            if (torrentRefreshInterval) {
                clearInterval(torrentRefreshInterval);
                torrentRefreshInterval = null;
            }
        }

        // --- Видео и удаление (с удалением timeline) ---
        async function deleteItem(path, isFolder = false, nameForConfirm) {
            const confirmMessage = isFolder 
                ? t('delete_folder_confirm', { name: nameForConfirm })
                : t('delete_file_confirm', { name: nameForConfirm });
            if (!confirm(confirmMessage)) return;
            try {
                const response = await fetch(`/remove.php?link=${encodeURIComponent(path)}`);
                if (!response.ok) throw new Error(await response.text());
                
                // Удаляем timeline для этого видео/папки (для папки можно удалить все внутри, но проще удалить по имени файла)
                if (!isFolder) {
                    await deleteTimelineFromServer(path);
                    timelinesMap.delete(path);
                } else {
                    // Для папки: удаляем все timeline для файлов внутри (пробегаем по videoItemsArray)
                    const filesToDelete = videoItemsArray.filter(v => v.path.startsWith(path + '/') || v.path === path);
                    for (const file of filesToDelete) {
                        await deleteTimelineFromServer(file.path);
                        timelinesMap.delete(file.path);
                    }
                }
                
                showStatusMessage(t('delete_success'), false);
                await fetchVideoList();
                await fetchDiskInfo();
                if (currentVideoPath && (currentVideoPath === path || currentVideoPath.startsWith(path + '/'))) {
                    resetVideoAreaToPlaceholder();
                    currentVideoPath = null;
                    currentDisplayName = "";
                    stopTimelineSaving();
                }
            } catch (err) {
                showStatusMessage(t('delete_error', { error: err.message }), true);
            }
        }

        function updateAllTexts() {
            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (key && translations[currentLang][key]) {
                    el.textContent = translations[currentLang][key];
                }
            });
            document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
                const key = el.getAttribute('data-i18n-placeholder');
                if (key && translations[currentLang][key]) {
                    el.placeholder = translations[currentLang][key];
                }
            });
            refreshBtn.innerHTML = t('btn_refresh');
            if (playlistSection.classList.contains('hidden-library')) {
                toggleLibraryBtn.innerHTML = t('btn_toggle_hide');
            } else {
                toggleLibraryBtn.innerHTML = t('btn_toggle_show');
            }
            if (!downloadCurrentBtn.classList.contains('hidden')) {
                downloadCurrentBtn.innerHTML = t('btn_download');
            }
            if (playbackStatusSpan.innerHTML.includes('✓') || playbackStatusSpan.innerHTML.includes('ready')) {
                playbackStatusSpan.innerHTML = t('status_ready');
            }
            const statusDiv = videoTreeContainer.querySelector('.status-message');
            if (statusDiv && videoItemsArray.length === 0) {
                if (statusDiv.innerHTML.includes('spinner')) {
                    statusDiv.innerHTML = `<span class="spinner"></span> ${t('loading_videos')}`;
                } else {
                    statusDiv.innerHTML = t('no_videos');
                }
            }
            if (!currentVideoPath) {
                currentVideoNameSpan.textContent = t('msg_select_video');
            }
            if (!unsupportedPlaceholder.classList.contains('hidden') && currentVideoPath) {
                const ext = getFileExtension(currentVideoPath).toUpperCase();
                unsupportedPlaceholder.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <span style="font-size: 3rem;">⚠️</span>
                        <strong>${t('unsupported_title', { ext })}</strong>
                        <p style="font-size: 0.85rem; color: #ccc;">${t('unsupported_desc', { ext })}</p>
                        <a href="${encodeURI(currentVideoPath)}" download class="download-current-btn" style="background:#4c6e8c; margin-top:8px;">${t('unsupported_download', { ext })}</a>
                    </div>
                `;
                currentVideoNameSpan.textContent = t('unsupported_only_download', { name: currentDisplayName, ext });
            } else if (videoPlayer.classList.contains('hidden') && !videoPlaceholder.classList.contains('hidden')) {
                const placeholderText = videoPlaceholder.querySelector('.video-placeholder-text');
                if (placeholderText) placeholderText.textContent = t('placeholder_text');
            }
            if (videoItemsArray.length > 0) {
                renderFullTree();
            } else if (videoItemsArray.length === 0 && videoTreeContainer.innerHTML !== '') {
                const emptyMsg = videoTreeContainer.querySelector('.status-message');
                if (emptyMsg) emptyMsg.textContent = t('no_videos');
            }
            if (!torrentPanel.classList.contains('hidden-torrent')) {
                addMagnetBtn.textContent = t('add_magnet');
                refreshTorrentsBtn.textContent = t('refresh_torrents');
                const fileLabel = document.querySelector('.file-input-label span');
                if (fileLabel) fileLabel.textContent = t('upload_torrent');
            }
            toggleTorrentBtn.innerHTML = torrentPanel.classList.contains('hidden-torrent') ? t('btn_toggle_torrent_show') : t('btn_toggle_torrent_hide');
        }

        function setLibraryVisible(visible) {
            if (visible) {
                playlistSection.classList.remove('hidden-library');
                toggleLibraryBtn.innerHTML = t('btn_toggle_show');
            } else {
                playlistSection.classList.add('hidden-library');
                toggleLibraryBtn.innerHTML = t('btn_toggle_hide');
            }
        }

        function toggleLibrary() {
            const isHidden = playlistSection.classList.contains('hidden-library');
            setLibraryVisible(isHidden);
        }

        function onVideoPlay() {
            if (!playlistSection.classList.contains('hidden-library')) {
                setLibraryVisible(false);
            }
        }

        function showVideoPlayerUI() {
            videoPlaceholder.classList.add('hidden');
            videoPlayer.classList.remove('hidden');
            unsupportedPlaceholder.classList.add('hidden');
        }

        function showUnsupportedUI(videoPath, displayName) {
            videoPlaceholder.classList.add('hidden');
            videoPlayer.classList.add('hidden');
            unsupportedPlaceholder.classList.remove('hidden');
            const ext = getFileExtension(videoPath).toUpperCase();
            unsupportedPlaceholder.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <span style="font-size: 3rem;">⚠️</span>
                    <strong>${t('unsupported_title', { ext })}</strong>
                    <p style="font-size: 0.85rem; color: #ccc;">${t('unsupported_desc', { ext })}</p>
                    <a href="${encodeURI(videoPath)}" download class="download-current-btn" style="background:#4c6e8c; margin-top:8px;">${t('unsupported_download', { ext })}</a>
                </div>
            `;
            currentVideoNameSpan.textContent = t('unsupported_only_download', { name: displayName, ext });
            downloadCurrentBtn.classList.add('hidden');
            showStatusMessage(t('unsupported_title', { ext }), true);
        }

        function resetVideoAreaToPlaceholder() {
            videoPlaceholder.classList.remove('hidden');
            videoPlayer.classList.add('hidden');
            unsupportedPlaceholder.classList.add('hidden');
            videoPlayer.removeAttribute('src');
            videoPlayer.load();
            currentVideoNameSpan.textContent = t('msg_select_video');
            downloadCurrentBtn.classList.add('hidden');
            const placeholderText = videoPlaceholder.querySelector('.video-placeholder-text');
            if (placeholderText) placeholderText.textContent = t('placeholder_text');
            stopTimelineSaving();
        }

        function showStatusMessage(text, isError = false) {
            playbackStatusSpan.innerHTML = isError ? `⚠️ ${text}` : `▶ ${text}`;
            playbackStatusSpan.style.color = isError ? "#ffaa88" : "#b9fbc0";
            if (!isError) {
                setTimeout(() => {
                    if (playbackStatusSpan.innerHTML.includes(text)) {
                        playbackStatusSpan.style.color = "";
                        playbackStatusSpan.innerHTML = t('status_ready');
                    }
                }, 2500);
            }
        }

        function hideApiError() { apiErrorDiv.classList.add('hidden'); apiErrorDiv.innerHTML = ''; }
        function showApiError(msg) { apiErrorDiv.innerHTML = `⚠️ ${msg}`; apiErrorDiv.classList.remove('hidden'); }

        function setListLoading() {
            videoTreeContainer.innerHTML = `<div class="status-message"><span class="spinner"></span> ${t('loading_videos')}</div>`;
        }
        function setListEmpty() {
            videoTreeContainer.innerHTML = `<div class="status-message">${t('no_videos')}</div>`;
        }

        // --- Построение дерева и рендер ---
        function buildTree(fileList) {
            const root = { files: [], children: {} };
            for (const item of fileList) {
                const path = item.path;
                let parts = path.split('/');
                if (parts.length > 0 && parts[0].toLowerCase() === ROOT_FOLDER_TO_SKIP.toLowerCase()) {
                    parts = parts.slice(1);
                }
                const fileName = parts.pop();
                const folderPath = parts;
                let currentNode = root;
                for (const folder of folderPath) {
                    if (!currentNode.children[folder]) {
                        currentNode.children[folder] = { files: [], children: {} };
                    }
                    currentNode = currentNode.children[folder];
                }
                currentNode.files.push({
                    path: path,
                    displayName: fileName,
                    ext: getFileExtension(path),
                    time: timelinesMap.get(path) || 0
                });
            }
            return root;
        }

        function renderTree(node, container) {
            for (const file of node.files) {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'video-item';
                if (currentVideoPath === file.path) fileDiv.classList.add('active');
                fileDiv.setAttribute('data-path', file.path);
                
                const iconSpan = document.createElement('span');
                iconSpan.className = 'video-icon';
                iconSpan.textContent = '🎬';
                const nameSpan = document.createElement('span');
                nameSpan.className = 'video-filename';
                nameSpan.textContent = file.displayName;
                
                const actionsDiv = document.createElement('div');
                actionsDiv.className = 'video-actions';
                
                const formatSpan = document.createElement('span');
                formatSpan.className = 'format-badge';
                formatSpan.textContent = file.ext.toUpperCase();
                actionsDiv.appendChild(formatSpan);
                
                // Показываем иконку таймлайна, если видео уже открывали (time > 0)
                if (file.time > 0) {
                    const timelineIcon = document.createElement('span');
                    timelineIcon.className = 'timeline-icon';
                    timelineIcon.innerHTML = '⏱️';
                    timelineIcon.title = `Продолжить с ${Math.floor(file.time / 60)}:${(file.time % 60).toString().padStart(2,'0')}`;
                    actionsDiv.appendChild(timelineIcon);
                }
                
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-btn';
                deleteBtn.innerHTML = t('btn_delete');
                deleteBtn.title = t('btn_delete');
                deleteBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    await deleteItem(file.path, false, file.displayName);
                });
                actionsDiv.appendChild(deleteBtn);
                
                fileDiv.appendChild(iconSpan);
                fileDiv.appendChild(nameSpan);
                fileDiv.appendChild(actionsDiv);
                
                fileDiv.addEventListener('click', (e) => {
                    if (!e.target.closest('.delete-btn')) {
                        playSelectedVideo(file.path, file.displayName);
                    }
                });
                container.appendChild(fileDiv);
            }
            
            const folderNames = Object.keys(node.children).sort();
            for (const folderName of folderNames) {
                const subNode = node.children[folderName];
                const folderWrapper = document.createElement('div');
                folderWrapper.className = 'tree-folder';
                
                const header = document.createElement('div');
                header.className = 'folder-header';
                header.innerHTML = `
                    <span class="folder-icon">📁</span>
                    <span class="folder-name">${escapeHtml(folderName)}</span>
                    <span class="toggle-icon">▶</span>
                `;
                const deleteFolderBtn = document.createElement('button');
                deleteFolderBtn.className = 'delete-btn';
                deleteFolderBtn.innerHTML = t('btn_delete');
                deleteFolderBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    let folderFullPath = null;
                    for (const item of videoItemsArray) {
                        if (item.path.includes(`/${folderName}/`) || item.path.endsWith(`/${folderName}`)) {
                            const parts = item.path.split('/');
                            let idx = parts.indexOf(folderName);
                            if (idx !== -1) {
                                folderFullPath = parts.slice(0, idx + 1).join('/');
                                break;
                            }
                        }
                    }
                    if (!folderFullPath) folderFullPath = folderName;
                    await deleteItem(folderFullPath, true, folderName);
                });
                const headerRight = document.createElement('div');
                headerRight.className = 'folder-actions';
                headerRight.appendChild(deleteFolderBtn);
                header.appendChild(headerRight);
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'folder-content collapsed';
                renderTree(subNode, contentDiv);
                const toggleIcon = header.querySelector('.toggle-icon');
                header.addEventListener('click', (e) => {
                    if (e.target.closest('.delete-btn')) return;
                    const isCollapsed = contentDiv.classList.contains('collapsed');
                    if (isCollapsed) {
                        contentDiv.classList.remove('collapsed');
                        toggleIcon.textContent = '▼';
                    } else {
                        contentDiv.classList.add('collapsed');
                        toggleIcon.textContent = '▶';
                    }
                });
                folderWrapper.appendChild(header);
                folderWrapper.appendChild(contentDiv);
                container.appendChild(folderWrapper);
            }
        }
        
        function escapeHtml(str) {
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        function renderFullTree() {
            if (!videoItemsArray.length) {
                setListEmpty();
                return;
            }
            const tree = buildTree(videoItemsArray);
            videoTreeContainer.innerHTML = '';
            renderTree(tree, videoTreeContainer);
        }

        function updateActiveClassByPath() {
            const allFileElements = document.querySelectorAll('.video-item');
            allFileElements.forEach(el => {
                const path = el.getAttribute('data-path');
                if (path === currentVideoPath) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }

        async function playSelectedVideo(videoPath, displayName) {
            if (!videoPath) return;
            currentVideoPath = videoPath;
            currentDisplayName = displayName;
            
            // Получаем сохраненное время и устанавливаем
            const savedTime = await getTimelineFromServer(videoPath);
            
            downloadCurrentBtn.classList.remove('hidden');
            downloadCurrentBtn.innerHTML = t('btn_download');
            downloadCurrentBtn.onclick = () => {
                window.location.href = encodeURI(videoPath);
            };
            currentVideoNameSpan.textContent = displayName;
            
            if (!isFormatSupported(videoPath)) {
                showUnsupportedUI(videoPath, displayName);
                updateActiveClassByPath();
                stopTimelineSaving();
                return;
            }
            showVideoPlayerUI();
            showStatusMessage(t('status_loading', { name: displayName }), false);
            const encodedSrc = encodeURI(videoPath);
            videoPlayer.pause();
            videoPlayer.removeAttribute('src');
            videoPlayer.load();
            videoPlayer.src = encodedSrc;
            videoPlayer.load();
            
            // Устанавливаем таймлайн после загрузки метаданных
            const onLoadedMetadata = () => {
                if (savedTime > 0 && savedTime < videoPlayer.duration) {
                    videoPlayer.currentTime = savedTime;
                    showStatusMessage(`⏱️ Продолжаем с ${Math.floor(savedTime / 60)}:${(savedTime % 60).toString().padStart(2,'0')}`, false);
                }
                videoPlayer.removeEventListener('loadedmetadata', onLoadedMetadata);
            };
            videoPlayer.addEventListener('loadedmetadata', onLoadedMetadata);
            
            try {
                await videoPlayer.play();
                showStatusMessage(t('status_playing', { name: displayName }), false);
                updateActiveClassByPath();
                // Запускаем автосохранение таймлайна
                startTimelineSaving(videoPath);
            } catch (playError) {
                let msg = t('status_autoplay_blocked');
                if (playError.name === 'NotSupportedError') msg = t('status_codec_error');
                showStatusMessage(msg, true);
                startTimelineSaving(videoPath);
            }
            const handleVideoError = () => {
                const errCode = videoPlayer.error?.code;
                let errorText = t('status_network_error');
                if (errCode === 4) errorText = t('status_404');
                else if (errCode === 3) errorText = t('status_decode_error');
                showStatusMessage(errorText, true);
                showUnsupportedUI(videoPath, displayName);
                videoPlayer.removeEventListener('error', handleVideoError);
                stopTimelineSaving();
            };
            videoPlayer.addEventListener('error', handleVideoError, { once: true });
        }
        
        async function fetchVideoList() {
            hideApiError();
            setListLoading();
            try {
                const resp = await fetch('/listing.php', { cache: 'no-cache' });
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                const data = await resp.json();
                if (!data || !Array.isArray(data.video)) {
                    throw new Error(t('api_invalid_format', { field: 'video' }));
                }
                let raw = data.video;
                // Фильтруем игнорируемые расширения
                raw = raw.filter(fullPath => !isIgnoredExtension(fullPath));
                if (!raw.length) {
                    videoItemsArray = [];
                    renderFullTree();
                    showApiError(t('api_empty_list'));
                    return;
                }
                videoItemsArray = raw.map(fullPath => ({ path: fullPath, displayName: fullPath }));
                // Загружаем все таймлайны для отображения иконок
                await refreshTimelinesMap();
                renderFullTree();
                showStatusMessage(t('status_ready'), false);
                if (currentVideoPath && !videoItemsArray.some(v => v.path === currentVideoPath)) {
                    currentVideoPath = null;
                    resetVideoAreaToPlaceholder();
                } else if (currentVideoPath) {
                    updateActiveClassByPath();
                }
            } catch (err) {
                console.error(err);
                showApiError(err.message || t('api_network_error'));
                setListEmpty();
                videoItemsArray = [];
            }
        }
        
        async function refreshAndKeep() {
            const wasPlaying = !videoPlayer.paused && videoPlayer.src && !videoPlayer.classList.contains('hidden');
            await fetchVideoList();
            if (wasPlaying && currentVideoPath && isFormatSupported(currentVideoPath)) {
                try { await videoPlayer.play(); } catch(e) {}
            }
        }
        
        function setLanguage(lang) {
            currentLang = lang;
            updateAllTexts();
            localStorage.setItem('player_lang', lang);
            langBtn.innerHTML = currentLang === 'ru' ? '🌐 EN' : '🌐 RU';
        }
        
        function toggleLanguage() {
            const newLang = currentLang === 'ru' ? 'en' : 'ru';
            setLanguage(newLang);
        }
        
        // --- ИНИЦИАЛИЗАЦИЯ ---
        async function init() {
            const savedLang = localStorage.getItem('player_lang');
            if (savedLang && (savedLang === 'ru' || savedLang === 'en')) {
                currentLang = savedLang;
            } else {
                currentLang = detectLanguage();
            }
            setLanguage(currentLang);
            
            videoPlayer.addEventListener('play', onVideoPlay);
            toggleLibraryBtn.addEventListener('click', toggleLibrary);
            
            if (toggleTorrentBtn && torrentPanel) {
                toggleTorrentBtn.addEventListener('click', () => {
                    const isHidden = torrentPanel.classList.contains('hidden-torrent');
                    if (isHidden) {
                        torrentPanel.classList.remove('hidden-torrent');
                        toggleTorrentBtn.innerHTML = t('btn_toggle_torrent_hide');
                        fetchDiskInfo();
                        fetchTorrents();
                        startTorrentRefresh();
                    } else {
                        torrentPanel.classList.add('hidden-torrent');
                        toggleTorrentBtn.innerHTML = t('btn_toggle_torrent_show');
                        stopTorrentRefresh();
                    }
                });
            }
            
            refreshBtn.addEventListener('click', async () => {
                refreshBtn.innerHTML = "⏳ ...";
                await refreshAndKeep();
                refreshBtn.innerHTML = t('btn_refresh');
            });
            langBtn.addEventListener('click', toggleLanguage);
            
            addMagnetBtn.addEventListener('click', () => addTorrentMagnet(magnetInput.value));
            refreshTorrentsBtn.addEventListener('click', () => { fetchTorrents(); fetchDiskInfo(); });
            torrentFileInput.addEventListener('change', (e) => {
                if (e.target.files.length) addTorrentFile(e.target.files[0]);
                torrentFileInput.value = '';
            });
            
            resetVideoAreaToPlaceholder();
            await fetchVideoList();
        }
        
        init();
    })();
</script>
</body>
</html>