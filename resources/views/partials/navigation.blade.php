<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-8">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">
                    Construction SaaS
                </a>
                
                <div class="hidden md:flex space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                        Дашборд
                    </a>
                    <a href="{{ route('projects.index') }}" class="text-gray-600 hover:text-gray-900">
                        Проекты
                    </a>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    {{ Auth::user()->name }} ({{ Auth::user()->role }})
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                        Выйти
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>