<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ProcessStudio Visual Flow Engine — DealSync</title>

    <!-- Google Fonts: Inter & Inter Tight -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/process-studio/index.jsx'])

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body, html {
            width: 100%;
            height: 100%;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        #process-studio-root {
            width: 100%;
            height: 100vh;
            min-height: 800px;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        /* Custom Node Glow & Animations */
        @keyframes flowPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.02); }
        }
        .react-flow__node.selected {
            animation: flowPulse 1.5s infinite ease-in-out;
        }
    </style>
</head>
<body class="antialiased w-full h-screen overflow-hidden bg-slate-50">
    <div id="process-studio-root"
         class="w-full h-screen min-h-[800px]"
         data-initial-flow="{{ request('flow', '') }}"
         data-csrf="{{ csrf_token() }}"
         data-api-base="{{ url('/api') }}"></div>
</body>
</html>
