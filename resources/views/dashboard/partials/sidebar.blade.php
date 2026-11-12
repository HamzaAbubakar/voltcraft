        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-900 border-r border-gray-800 p-4 flex flex-col justify-between shrink-0">
            <div class="space-y-1">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Core Modules</p>
                
                <a href="{{ route('dashboard') }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    <i class="fas fa-columns text-base"></i>
                    <span>Dashboard Ledger</span>
                </a>

                @if (auth()->user()->canAccessPage('inventory'))
                    <a href="{{ route('inventory.index') }}" aria-current="{{ request()->routeIs('inventory.index') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.index') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-boxes text-base"></i>
                        <span>Inventory Management</span>
                    </a>

                    <a href="{{ route('inventory.battery-models.index') }}" aria-current="{{ request()->routeIs('inventory.battery-models.*') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('inventory.battery-models.*') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-battery-three-quarters text-base"></i>
                        <span>Battery Model Mapping</span>
                    </a>
                @endif

                @if (auth()->user()->canAccessPage('cash'))
                    <a href="{{ route('cash.index') }}" aria-current="{{ request()->routeIs('cash.index') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('cash.index') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-coins text-base"></i>
                        <span>Cash In and Out</span>
                    </a>
                @endif

                @if (auth()->user()->canAccessPage('workforce'))
                    <a href="{{ route('workforce.index') }}" aria-current="{{ request()->routeIs('workforce.index') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('workforce.index') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-users-cog text-base"></i>
                        <span>Workforce & HR</span>
                    </a>
                @endif

                @if (auth()->user()->canAccessPage('expenses'))
                    <a href="{{ route('expenses.index') }}" aria-current="{{ request()->routeIs('expenses.index') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('expenses.index') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-wallet text-base"></i>
                        <span>Expenses</span>
                    </a>
                @endif

                @if (auth()->user()->canAccessPage('projects'))
                    <a href="{{ route('projects.index') }}" aria-current="{{ request()->routeIs('projects.*') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('projects.*') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-tasks text-base"></i>
                        <span>Projects</span>
                    </a>
                @endif

                <button type="button" disabled title="Panel coming soon" class="w-full flex cursor-not-allowed items-center space-x-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-500 opacity-80">
                    <i class="fas fa-share-alt text-base"></i>
                    <span>Company Shares</span>
                    <span class="ml-auto text-[9px] uppercase tracking-wider text-gray-600">Soon</span>
                </button>

                @if (auth()->user()->canAccessPage('investments'))
                    <a href="{{ route('investments.index') }}" aria-current="{{ request()->routeIs('investments.*') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('investments.*') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-chart-line text-base"></i>
                        <span>Investments</span>
                    </a>
                @endif

                @if (auth()->user()->is_admin)
                    <a href="{{ route('users.index') }}" aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <i class="fas fa-user-shield text-base"></i>
                        <span>User Management</span>
                    </a>
                @endif

            </div>
            
            <div class="bg-gray-950/50 p-3 rounded-xl border border-gray-800">
                <div class="flex items-center space-x-2 text-xs text-gray-400 mb-1">
                    <i class="fas fa-info-circle text-amber-500"></i>
                    <span class="font-medium">Low Stock Rule</span>
                </div>
                <p class="text-xs text-gray-500">Items at or below their reorder point appear in the dashboard watchlist.</p>
            </div>
        </aside>
