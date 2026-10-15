<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Voltcraft ERP') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111827; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #4B5563; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased custom-scrollbar">

    <!-- Top Navigation Bar -->
    <header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="bg-amber-500 text-gray-950 p-2 rounded-xl font-black text-xl shadow-lg shadow-amber-500/10">
                <i class="fas fa-charging-station"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-white">VOLT<span class="text-amber-500">CRAFT</span> ERP</h1>
                <p class="text-xs text-gray-400">Solar Battery Assembly & Management Suite</p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-gray-200">{{ auth()->user()->name }}</p>
                <p class="text-xs text-emerald-400 flex items-center justify-end"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1.5 animate-pulse"></span>System Live</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-300 transition hover:border-amber-500 hover:text-amber-400">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sign out
                </button>
            </form>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-73px)]">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-900 border-r border-gray-800 p-4 flex flex-col justify-between shrink-0">
            <div class="space-y-1">
                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Core Modules</p>
                
                <button onclick="switchTab('dashboard')" id="btn-dashboard" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all bg-amber-500 text-gray-950 shadow-lg shadow-amber-500/10 font-semibold">
                    <i class="fas fa-columns text-base"></i>
                    <span>Dashboard Ledger</span>
                </button>

                <button onclick="switchTab('inventory')" id="btn-inventory" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:bg-gray-800 hover:text-white">
                    <i class="fas fa-boxes text-base"></i>
                    <span>Inventory Management</span>
                </button>

                
                <button onclick="switchTab('cash')" id="btn-cash" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:bg-gray-800 hover:text-white">
                    <i class="fas fa-coins text-base"></i>
                    <span>Cash In and Out</span>
                </button>

                <button onclick="switchTab('hr')" id="btn-hr" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:bg-gray-800 hover:text-white">
                    <i class="fas fa-users-cog text-base"></i>
                    <span>Workforce & HR</span>
                </button>

                <button onclick="switchTab('expenses')" id="btn-expenses" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:bg-gray-800 hover:text-white">
                    <i class="fas fa-wallet text-base"></i>
                    <span>Expenses</span>
                </button>

                <button type="button" disabled title="Panel coming soon" class="w-full flex cursor-not-allowed items-center space-x-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-500 opacity-80">
                    <i class="fas fa-tasks text-base"></i>
                    <span>Projects</span>
                    <span class="ml-auto text-[9px] uppercase tracking-wider text-gray-600">Soon</span>
                </button>

                <button type="button" disabled title="Panel coming soon" class="w-full flex cursor-not-allowed items-center space-x-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-500 opacity-80">
                    <i class="fas fa-share-alt text-base"></i>
                    <span>Company Shares</span>
                    <span class="ml-auto text-[9px] uppercase tracking-wider text-gray-600">Soon</span>
                </button>

                <button type="button" disabled title="Panel coming soon" class="w-full flex cursor-not-allowed items-center space-x-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-500 opacity-80">
                    <i class="fas fa-chart-line text-base"></i>
                    <span>Investments</span>
                    <span class="ml-auto text-[9px] uppercase tracking-wider text-gray-600">Soon</span>
                </button>

            </div>
            
            <div class="bg-gray-950/50 p-3 rounded-xl border border-gray-800">
                <div class="flex items-center space-x-2 text-xs text-gray-400 mb-1">
                    <i class="fas fa-info-circle text-amber-500"></i>
                    <span class="font-medium">Assembly Warning Threshold</span>
                </div>
                <p class="text-xs text-gray-500">Global buffer limits currently locked at 15% minimum safety capacity.</p>
            </div>
        </aside>

        <!-- Main Body Workspace -->
        <main class="flex-1 p-6 lg:p-8 space-y-8 overflow-x-hidden">

            <!-- SECTION 1: DASHBOARD LEDGER TAB -->
            <div id="tab-dashboard" class="space-y-8">
                <!-- Row 1: Low Stock Alerts Header Banner -->
                <div class="bg-gradient-to-r from-red-950/30 to-amber-950/20 border border-red-900/50 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <h2 class="text-lg font-bold text-red-200">Critical Low Stock Inventory Watchlist</h2>
                        </div>
                        <span class="text-xs bg-red-900/60 text-red-200 border border-red-700/50 px-2.5 py-1 rounded-full font-medium">Action Required</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-900/80 border border-gray-800 p-4 rounded-xl flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-200">3.2V 280Ah Prismatic Cells</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Primary Assembly Core</p>
                                </div>
                                <span class="text-xs bg-red-950 text-red-400 px-2 py-0.5 rounded font-mono border border-red-900">420 pcs</span>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Stock Buffer Level</span>
                                    <span class="text-red-400 font-medium">21% remaining</span>
                                </div>
                                <div class="w-full bg-gray-800 rounded-full h-1.5">
                                    <div class="bg-red-500 h-1.5 rounded-full" style="width: 21%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-900/80 border border-gray-800 p-4 rounded-xl flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-200">100A Smart BMS Units (JK)</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Control Boards</p>
                                </div>
                                <span class="text-xs bg-red-950 text-red-400 px-2 py-0.5 rounded font-mono border border-red-900">14 pcs</span>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Stock Buffer Level</span>
                                    <span class="text-red-400 font-medium">9% remaining</span>
                                </div>
                                <div class="w-full bg-gray-800 rounded-full h-1.5">
                                    <div class="bg-red-500 h-1.5 rounded-full" style="width: 9%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-900/80 border border-gray-800 p-4 rounded-xl flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-200">Heavy Copper Busbars</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Intercell Solid Connectors</p>
                                </div>
                                <span class="text-xs bg-amber-950 text-amber-400 px-2 py-0.5 rounded font-mono border border-amber-900">110 pcs</span>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Stock Buffer Level</span>
                                    <span class="text-amber-400 font-medium">36% remaining</span>
                                </div>
                                <div class="w-full bg-gray-800 rounded-full h-1.5">
                                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: 36%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Battery Assembly Units Sold Breakdown -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Battery Type Sales Volume Breakdown (This Month)</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-900 border border-gray-800 p-5 rounded-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/5 rounded-full -mr-6 -mt-6"></div>
                            <p class="text-xs font-medium text-gray-400">12V Standard Packs</p>
                            <div class="flex items-baseline space-x-2 mt-2">
                                <span class="text-2xl font-bold tracking-tight text-white">142</span>
                                <span class="text-xs font-semibold text-emerald-400"><i class="fas fa-arrow-up"></i> 12%</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Primarily marine & small RV installations</p>
                        </div>
                        <div class="bg-gray-900 border border-gray-800 p-5 rounded-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-teal-500/5 rounded-full -mr-6 -mt-6"></div>
                            <p class="text-xs font-medium text-gray-400">24V Heavy Duty Packs</p>
                            <div class="flex items-baseline space-x-2 mt-2">
                                <span class="text-2xl font-bold tracking-tight text-white">89</span>
                                <span class="text-xs font-semibold text-emerald-400"><i class="fas fa-arrow-up"></i> 8%</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Truck systems & localized off-grid setups</p>
                        </div>
                        <div class="bg-gray-900 border border-gray-800 p-5 rounded-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/5 rounded-full -mr-6 -mt-6"></div>
                            <p class="text-xs font-medium text-gray-400">48V Type A (Wall Mount)</p>
                            <div class="flex items-baseline space-x-2 mt-2">
                                <span class="text-2xl font-bold tracking-tight text-white">214</span>
                                <span class="text-xs font-semibold text-emerald-400"><i class="fas fa-arrow-up"></i> 24%</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Residential Solar Powerwall units</p>
                        </div>
                        <div class="bg-gray-900 border border-gray-800 p-5 rounded-2xl relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/5 rounded-full -mr-6 -mt-6"></div>
                            <p class="text-xs font-medium text-gray-400">48V Type B (Server Rack)</p>
                            <div class="flex items-baseline space-x-2 mt-2">
                                <span class="text-2xl font-bold tracking-tight text-white">165</span>
                                <span class="text-xs font-semibold text-red-400"><i class="fas fa-arrow-down"></i> 3%</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Data centers & commercial stack arrays</p>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Salesperson Accounts Managed Ledgers -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Salesperson Accounts & Held Capital Pipeline</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Salesperson 1 -->
                        <div class="bg-gradient-to-b from-gray-900 to-gray-950 border border-gray-800 rounded-2xl p-5 shadow-xl">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-800/60">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-sm">AK</div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Asif Khan</h4>
                                        <p class="text-xs text-gray-400">Senior Accounts Rep</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20">Active</span>
                            </div>
                            <div class="py-4 space-y-3">
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase tracking-wide">Current Account Balance</p>
                                    <p class="text-2xl font-mono font-bold text-emerald-400 mt-0.5">Rs 14,250.00</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                    <div class="bg-gray-900/50 p-2 rounded-lg border border-gray-850">
                                        <span class="text-gray-400 block text-[10px] uppercase">Total Sales Generated</span>
                                        <span class="text-white font-mono font-semibold">Rs 52,400</span>
                                    </div>
                                    <div class="bg-gray-900/50 p-2 rounded-lg border border-gray-850">
                                        <span class="text-gray-400 block text-[10px] uppercase">Supplier Deductions</span>
                                        <span class="text-red-400 font-mono font-semibold">Rs 38,150</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Salesperson 2 -->
                        <div class="bg-gradient-to-b from-gray-900 to-gray-950 border border-gray-800 rounded-2xl p-5 shadow-xl">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-800/60">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-sm">ZM</div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Zainab Malik</h4>
                                        <p class="text-xs text-gray-400">Regional Accounts Rep</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20">Active</span>
                            </div>
                            <div class="py-4 space-y-3">
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase tracking-wide">Current Account Balance</p>
                                    <p class="text-2xl font-mono font-bold text-emerald-400 mt-0.5">Rs 8,910.00</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                    <div class="bg-gray-900/50 p-2 rounded-lg border border-gray-850">
                                        <span class="text-gray-400 block text-[10px] uppercase">Total Sales Generated</span>
                                        <span class="text-white font-mono font-semibold">Rs 41,200</span>
                                    </div>
                                    <div class="bg-gray-900/50 p-2 rounded-lg border border-gray-850">
                                        <span class="text-gray-400 block text-[10px] uppercase">Supplier Deductions</span>
                                        <span class="text-red-400 font-mono font-semibold">Rs 32,290</span>
                                    </div>
                                end</div>
                            </div>
                        </div>

                        <!-- Salesperson 3 -->
                        <div class="bg-gradient-to-b from-gray-900 to-gray-950 border border-gray-800 rounded-2xl p-5 shadow-xl">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-800/60">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-sm">BA</div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Bilal Ahmed</h4>
                                        <p class="text-xs text-gray-400">Accounts Executive</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded-full border border-emerald-500/20">Active</span>
                            </div>
                            <div class="py-4 space-y-3">
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase tracking-wide">Current Account Balance</p>
                                    <p class="text-2xl font-mono font-bold text-emerald-400 mt-0.5">Rs 5,430.00</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                                    <div class="bg-gray-900/50 p-2 rounded-lg border border-gray-850">
                                        <span class="text-gray-400 block text-[10px] uppercase">Total Sales Generated</span>
                                        <span class="text-white font-mono font-semibold">Rs 29,500</span>
                                    </div>
                                    <div class="bg-gray-900/50 p-2 rounded-lg border border-gray-850">
                                        <span class="text-gray-400 block text-[10px] uppercase">Supplier Deductions</span>
                                        <span class="text-red-400 font-mono font-semibold">Rs 24,070</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Transaction Entry Console & Real-time Ledger Logs -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Posting Console -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 h-fit">
                        <div class="flex items-center space-x-2 pb-3 border-b border-gray-800 mb-4">
                            <i class="fas fa-sliders-h text-amber-500"></i>
                            <h4 class="font-bold text-white text-sm">Post New Capital Flow Activity</h4>
                        </div>
                        <form class="space-y-4" onsubmit="event.preventDefault();">
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">Target Account</label>
                                <select class="w-full bg-gray-950 border border-gray-800 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-amber-500">
                                    <option>Asif Khan</option>
                                    <option>Zainab Malik</option>
                                    <option>Bilal Ahmed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">Transaction Category</label>
                                <select class="w-full bg-gray-950 border border-gray-800 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-amber-500">
                                    <option class="text-emerald-400">Credit: Battery Pack Unit Sale Addition</option>
                                    <option class="text-red-400">Debit: Payment to Raw Inventory Supplier</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">Battery Selection / Spec Link</label>
                                <select class="w-full bg-gray-950 border border-gray-800 rounded-lg p-2.5 text-sm text-gray-200 focus:outline-none focus:border-amber-500">
                                    <option>48V Type A (Wall Mount)</option>
                                    <option>48V Type B (Server Rack)</option>
                                    <option>24V Heavy Duty Pack</option>
                                    <option>12V Standard Pack</option>
                                    <option>Not Applicable (Supplier Materials Buy)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-400 mb-1">Capital Value Amount (USD)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500 text-sm">Rs </span>
                                    <input type="number" placeholder="0.00" class="w-full bg-gray-950 border border-gray-800 rounded-lg pl-7 pr-3 py-2 text-sm text-gray-200 focus:outline-none focus:border-amber-500 font-mono">
                                </div>
                            </div>
                            <button class="w-full bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold py-2.5 px-4 rounded-lg text-sm transition-all shadow-lg shadow-amber-500/10 mt-2">
                                Log Entry into Active Ledger
                            </button>
                        </form>
                    </div>

                    <!-- Ledger Table Grid -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 lg:col-span-2 overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-gray-800 mb-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-history text-amber-500"></i>
                                    <h4 class="font-bold text-white text-sm">Real-time Account Activity Logs</h4>
                                </div>
                                <span class="text-[11px] text-gray-400 font-mono">Showing last 4 entries</span>
                            </div>
                            
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="text-gray-400 border-b border-gray-800">
                                            <th class="pb-3 font-semibold">Timestamp</th>
                                            <th class="pb-3 font-semibold">Salesperson</th>
                                            <th class="pb-3 font-semibold">Activity Details</th>
                                            <th class="pb-3 font-semibold text-right">Impact Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800/50">
                                        <tr>
                                            <td class="py-3 font-mono text-gray-500">Oct 04, 15:42</td>
                                            <td class="py-3 font-medium text-gray-200">Asif Khan</td>
                                            <td class="py-3">
                                                <span class="bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded text-[10px] font-medium mr-2">SUPPLIER DEDUCTION</span>
                                                Deducted for Prismatic Cell Batch #9922 (Vendor Payment)
                                            </td>
                                            <td class="py-3 text-right font-mono font-bold text-red-400">-Rs 3,400.00</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 font-mono text-gray-500">Oct 04, 14:15</td>
                                            <td class="py-3 font-medium text-gray-200">Zainab Malik</td>
                                            <td class="py-3">
                                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded text-[10px] font-medium mr-2">UNIT SALE MATCH</span>
                                                Sold x2 48V Type B (Server Rack Wall Array)
                                            </td>
                                            <td class="py-3 text-right font-mono font-bold text-emerald-400">+Rs 6,200.00</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 font-mono text-gray-500">Oct 04, 11:02</td>
                                            <td class="py-3 font-medium text-gray-200">Bilal Ahmed</td>
                                            <td class="py-3">
                                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded text-[10px] font-medium mr-2">UNIT SALE MATCH</span>
                                                Sold x1 24V Heavy Duty Custom Build Pack
                                            </td>
                                            <td class="py-3 text-right font-mono font-bold text-emerald-400">+Rs 2,450.00</td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 font-mono text-gray-500">Oct 03, 16:50</td>
                                            <td class="py-3 font-medium text-gray-200">Asif Khan</td>
                                            <td class="py-3">
                                                <span class="bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded text-[10px] font-medium mr-2">SUPPLIER DEDUCTION</span>
                                                Deducted for JK-BMS Control Board Batch Order
                                            </td>
                                            <td class="py-3 text-right font-mono font-bold text-red-400">-Rs 1,150.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-800 text-center">
                            <a href="#" class="text-xs font-semibold text-amber-500 hover:text-amber-400 flex items-center justify-center space-x-1">
                                <span>Access Full Operational Ledger Archive</span>
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


            <!-- SECTION 2: INVENTORY MANAGEMENT MODULE TAB -->
            <div id="tab-inventory" class="space-y-8 hidden">
                <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-medium text-amber-400">
                            <i class="fas fa-boxes-stacked"></i>
                            <span>STOCK CONTROL</span>
                        </div>
                        <h2 class="mt-1 text-xl font-bold text-white">Inventory Management</h2>
                        <p class="mt-0.5 text-xs text-gray-400">Track assembly materials, stock levels, and incoming purchase orders</p>
                    </div>
                    <button class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-colors hover:bg-amber-400">
                        <i class="fas fa-plus mr-2"></i>Receive Stock
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                        <div class="flex items-center justify-between text-xs text-gray-400"><span>Total SKUs</span><i class="fas fa-barcode text-amber-400"></i></div>
                        <p class="mt-2 text-2xl font-bold text-white">248</p>
                        <p class="mt-1 text-[11px] text-gray-500">Across 6 stock categories</p>
                    </div>
                    <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                        <div class="flex items-center justify-between text-xs text-gray-400"><span>Inventory Value</span><i class="fas fa-coins text-emerald-400"></i></div>
                        <p class="mt-2 text-2xl font-bold text-white">Rs 184,620</p>
                        <p class="mt-1 text-[11px] text-emerald-400"><i class="fas fa-arrow-up mr-1"></i>4.8% from last month</p>
                    </div>
                    <div class="rounded-xl border border-red-900/60 bg-red-950/20 p-4">
                        <div class="flex items-center justify-between text-xs text-gray-400"><span>Low Stock Items</span><i class="fas fa-triangle-exclamation text-red-400"></i></div>
                        <p class="mt-2 text-2xl font-bold text-red-300">12</p>
                        <p class="mt-1 text-[11px] text-red-400">4 need immediate reorder</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-300">All Items <span class="ml-1 text-amber-500">248</span></button>
                        <button class="rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs font-medium text-gray-400 transition-colors hover:text-white">Low Stock <span class="ml-1 text-red-400">12</span></button>
                        <button class="rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs font-medium text-gray-400 transition-colors hover:text-white">On Order <span class="ml-1 text-sky-400">7</span></button>
                    </div>
                    <div class="flex gap-2">
                        <label class="flex min-w-0 items-center gap-2 rounded-lg border border-gray-800 bg-gray-900 px-3 text-gray-500">
                            <i class="fas fa-search text-xs"></i>
                            <input type="search" placeholder="Search inventory..." aria-label="Search inventory" class="w-full bg-transparent py-2 text-xs text-gray-200 outline-none placeholder:text-gray-500 sm:w-48">
                        </label>
                        <button aria-label="Filter inventory" title="Filter inventory" class="rounded-lg border border-gray-800 bg-gray-900 px-3 text-gray-300 transition-colors hover:bg-gray-800"><i class="fas fa-sliders"></i></button>
                        <button aria-label="Export inventory" title="Export inventory" class="rounded-lg border border-gray-800 bg-gray-900 px-3 text-gray-300 transition-colors hover:bg-gray-800"><i class="fas fa-download"></i></button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Stock Items</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Current quantities across all warehouse locations</p>
                        </div>
                        <span class="hidden text-xs text-gray-500 sm:block">Updated just now</span>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[900px] border-collapse text-left text-xs">
                            <thead>
                                <tr class="border-b border-gray-800 text-gray-500">
                                    <th class="px-5 py-3 font-semibold">Item</th>
                                    <th class="px-4 py-3 font-semibold">Category</th>
                                    <th class="px-4 py-3 text-right font-semibold">On hand</th>
                                    <th class="px-4 py-3 text-right font-semibold">Reorder point</th>
                                    <th class="px-4 py-3 font-semibold">Stock level</th>
                                    <th class="px-4 py-3 font-semibold">Location</th>
                                    <th class="px-5 py-3 text-right font-semibold">Unit cost</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/70">
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-5 py-3.5"><p class="font-semibold text-gray-200">3.2V 280Ah Prismatic Cell</p><p class="mt-0.5 font-mono text-[10px] text-gray-500">CELL-LFP-280</p></td>
                                    <td class="px-4 py-3.5 text-gray-400">Battery Cells</td>
                                    <td class="px-4 py-3.5 text-right font-mono font-semibold text-red-400">420 pcs</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-gray-400">500 pcs</td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-2"><div class="h-1.5 w-20 rounded-full bg-gray-800"><div class="h-1.5 w-[42%] rounded-full bg-red-500"></div></div><span class="text-[10px] font-medium text-red-400">Low</span></div></td>
                                    <td class="px-4 py-3.5 text-gray-400">Warehouse A · R1</td>
                                    <td class="px-5 py-3.5 text-right font-mono text-gray-300">Rs 38.50</td>
                                </tr>
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-5 py-3.5"><p class="font-semibold text-gray-200">100A Smart BMS Unit (JK)</p><p class="mt-0.5 font-mono text-[10px] text-gray-500">BMS-JK-100A</p></td>
                                    <td class="px-4 py-3.5 text-gray-400">Electronics</td>
                                    <td class="px-4 py-3.5 text-right font-mono font-semibold text-red-400">14 pcs</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-gray-400">40 pcs</td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-2"><div class="h-1.5 w-20 rounded-full bg-gray-800"><div class="h-1.5 w-[18%] rounded-full bg-red-500"></div></div><span class="text-[10px] font-medium text-red-400">Critical</span></div></td>
                                    <td class="px-4 py-3.5 text-gray-400">Warehouse B · E2</td>
                                    <td class="px-5 py-3.5 text-right font-mono text-gray-300">Rs 86.00</td>
                                </tr>
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-5 py-3.5"><p class="font-semibold text-gray-200">Heavy Copper Busbar Set</p><p class="mt-0.5 font-mono text-[10px] text-gray-500">CON-BAR-CU-04</p></td>
                                    <td class="px-4 py-3.5 text-gray-400">Connectors</td>
                                    <td class="px-4 py-3.5 text-right font-mono font-semibold text-amber-300">110 sets</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-gray-400">100 sets</td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-2"><div class="h-1.5 w-20 rounded-full bg-gray-800"><div class="h-1.5 w-[58%] rounded-full bg-amber-500"></div></div><span class="text-[10px] font-medium text-amber-400">Watch</span></div></td>
                                    <td class="px-4 py-3.5 text-gray-400">Warehouse A · C3</td>
                                    <td class="px-5 py-3.5 text-right font-mono text-gray-300">Rs 12.40</td>
                                </tr>
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-5 py-3.5"><p class="font-semibold text-gray-200">ABS Battery Enclosure 48V</p><p class="mt-0.5 font-mono text-[10px] text-gray-500">CASE-ABS-48</p></td>
                                    <td class="px-4 py-3.5 text-gray-400">Enclosures</td>
                                    <td class="px-4 py-3.5 text-right font-mono font-semibold text-gray-200">286 pcs</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-gray-400">120 pcs</td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-2"><div class="h-1.5 w-20 rounded-full bg-gray-800"><div class="h-1.5 w-[78%] rounded-full bg-emerald-500"></div></div><span class="text-[10px] font-medium text-emerald-400">Healthy</span></div></td>
                                    <td class="px-4 py-3.5 text-gray-400">Warehouse C · P4</td>
                                    <td class="px-5 py-3.5 text-right font-mono text-gray-300">Rs 24.75</td>
                                </tr>
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-5 py-3.5"><p class="font-semibold text-gray-200">12 AWG Silicone Wire, Red</p><p class="mt-0.5 font-mono text-[10px] text-gray-500">WIRE-SI-12-R</p></td>
                                    <td class="px-4 py-3.5 text-gray-400">Wiring</td>
                                    <td class="px-4 py-3.5 text-right font-mono font-semibold text-gray-200">1,840 m</td>
                                    <td class="px-4 py-3.5 text-right font-mono text-gray-400">600 m</td>
                                    <td class="px-4 py-3.5"><div class="flex items-center gap-2"><div class="h-1.5 w-20 rounded-full bg-gray-800"><div class="h-1.5 w-[72%] rounded-full bg-emerald-500"></div></div><span class="text-[10px] font-medium text-emerald-400">Healthy</span></div></td>
                                    <td class="px-4 py-3.5 text-gray-400">Warehouse B · W1</td>
                                    <td class="px-5 py-3.5 text-right font-mono text-gray-300">Rs 0.82 / m</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-gray-800 px-5 py-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                        <span>Showing <span class="font-medium text-gray-300">5</span> of <span class="font-medium text-gray-300">248</span> items</span>
                        <div class="flex items-center gap-2"><button class="rounded border border-gray-800 px-2.5 py-1.5 text-gray-500" disabled>Previous</button><span class="px-2 text-gray-300">1 / 50</span><button class="rounded border border-gray-700 px-2.5 py-1.5 text-gray-300 hover:bg-gray-800">Next</button></div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: CASH IN AND OUT MODULE TAB -->
            <div id="tab-cash" class="space-y-8 hidden">
                <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-medium text-emerald-400">
                            <i class="fas fa-money-bill-transfer"></i>
                            <span>CASH POSITION</span>
                        </div>
                        <h2 class="mt-1 text-xl font-bold text-white">Cash In and Out</h2>
                        <p class="mt-0.5 text-xs text-gray-400">Track sales receipts, supplier deductions, and balances held by each salesperson</p>
                    </div>
                    <button class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-colors hover:bg-amber-400">
                        <i class="fas fa-plus mr-2"></i>Record Transaction
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                        <div class="flex items-center justify-between text-xs text-gray-400"><span>Cash Held by Sales Team</span><i class="fas fa-wallet text-amber-400"></i></div>
                        <p class="mt-2 text-2xl font-bold text-white">Rs 28,590</p>
                        <p class="mt-1 text-[11px] text-gray-500">Combined current balances</p>
                    </div>
                    <div class="rounded-xl border border-emerald-900/50 bg-emerald-950/20 p-4">
                        <div class="flex items-center justify-between text-xs text-gray-400"><span>Cash In · Sales Generated</span><i class="fas fa-arrow-down-to-line text-emerald-400"></i></div>
                        <p class="mt-2 text-2xl font-bold text-emerald-300">Rs 123,100</p>
                        <p class="mt-1 text-[11px] text-gray-500">Across all 3 salespeople</p>
                    </div>
                    <div class="rounded-xl border border-red-900/50 bg-red-950/20 p-4">
                        <div class="flex items-center justify-between text-xs text-gray-400"><span>Cash Out · Supplier Deductions</span><i class="fas fa-arrow-up-from-line text-red-400"></i></div>
                        <p class="mt-2 text-2xl font-bold text-red-300">Rs 94,510</p>
                        <p class="mt-1 text-[11px] text-gray-500">Deducted from salesperson ledgers</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.5fr_1fr]">
                    <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-white">Sales and Supplier Deductions by Salesperson</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Totals match the dashboard account ledgers</p>
                        </div>
                        <div class="space-y-5">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs"><span class="font-medium text-gray-200">Asif Khan</span><span class="font-mono text-gray-400">Rs 52,400 in <span class="mx-1 text-gray-600">/</span> <span class="text-red-400">Rs 38,150 out</span></span></div>
                                <div class="space-y-1.5"><div class="h-2 rounded-full bg-gray-800"><div class="h-2 w-full rounded-full bg-emerald-500"></div></div><div class="h-1.5 rounded-full bg-gray-800"><div class="h-1.5 w-[73%] rounded-full bg-red-500/80"></div></div></div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs"><span class="font-medium text-gray-200">Zainab Malik</span><span class="font-mono text-gray-400">Rs 41,200 in <span class="mx-1 text-gray-600">/</span> <span class="text-red-400">Rs 32,290 out</span></span></div>
                                <div class="space-y-1.5"><div class="h-2 rounded-full bg-gray-800"><div class="h-2 w-[79%] rounded-full bg-emerald-500"></div></div><div class="h-1.5 rounded-full bg-gray-800"><div class="h-1.5 w-[62%] rounded-full bg-red-500/80"></div></div></div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-xs"><span class="font-medium text-gray-200">Bilal Ahmed</span><span class="font-mono text-gray-400">Rs 29,500 in <span class="mx-1 text-gray-600">/</span> <span class="text-red-400">Rs 24,070 out</span></span></div>
                                <div class="space-y-1.5"><div class="h-2 rounded-full bg-gray-800"><div class="h-2 w-[56%] rounded-full bg-emerald-500"></div></div><div class="h-1.5 rounded-full bg-gray-800"><div class="h-1.5 w-[46%] rounded-full bg-red-500/80"></div></div></div>
                            </div>
                        </div>
                        <div class="mt-5 flex flex-wrap items-center gap-5 border-t border-gray-800 pt-4 text-[11px] text-gray-400"><span class="flex items-center gap-2"><i class="h-2 w-2 rounded-sm bg-emerald-500"></i>Sales / cash in</span><span class="flex items-center gap-2"><i class="h-2 w-2 rounded-sm bg-red-500"></i>Supplier deductions / cash out</span><span class="ml-auto font-medium text-gray-300">Net held: Rs 28,590</span></div>
                    </div>

                    <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-white">Salesperson Balances</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Current cash held in each account</p>
                        </div>
                        <div class="divide-y divide-gray-800">
                            <div class="flex items-center justify-between py-3"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-xs font-bold text-amber-400">AK</span><div><p class="text-xs font-semibold text-gray-200">Asif Khan</p><p class="mt-0.5 text-[10px] text-gray-500">Senior Accounts Rep</p></div></div><span class="font-mono text-xs font-semibold text-emerald-400">Rs 14,250.00</span></div>
                            <div class="flex items-center justify-between py-3"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-xs font-bold text-emerald-400">ZM</span><div><p class="text-xs font-semibold text-gray-200">Zainab Malik</p><p class="mt-0.5 text-[10px] text-gray-500">Regional Accounts Rep</p></div></div><span class="font-mono text-xs font-semibold text-emerald-400">Rs 8,910.00</span></div>
                            <div class="flex items-center justify-between py-3"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-xs font-bold text-sky-400">BA</span><div><p class="text-xs font-semibold text-gray-200">Bilal Ahmed</p><p class="mt-0.5 text-[10px] text-gray-500">Accounts Executive</p></div></div><span class="font-mono text-xs font-semibold text-emerald-400">Rs 5,430.00</span></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between border-t border-gray-800 pt-3 text-xs"><span class="font-medium text-gray-400">Combined balance</span><span class="font-mono font-bold text-white">Rs 28,590.00</span></div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                    <div class="flex flex-col gap-3 border-b border-gray-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div><h3 class="text-sm font-semibold text-white">Recent Transactions</h3><p class="mt-0.5 text-xs text-gray-500">Sales receipts and supplier deductions by salesperson</p></div>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-800 bg-gray-950 px-3 text-gray-500"><i class="fas fa-search text-xs"></i><input type="search" aria-label="Search transactions" placeholder="Search transactions..." class="w-full bg-transparent py-2 text-xs text-gray-200 outline-none placeholder:text-gray-500 sm:w-48"></label>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[760px] border-collapse text-left text-xs">
                            <thead><tr class="border-b border-gray-800 text-gray-500"><th class="px-5 py-3 font-semibold">Salesperson</th><th class="px-4 py-3 font-semibold">Cash activity</th><th class="px-4 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Direction</th><th class="px-4 py-3 text-right font-semibold">Amount</th><th class="px-5 py-3 text-right font-semibold">Balance impact</th></tr></thead>
                            <tbody class="divide-y divide-gray-800/70">
                                <tr class="hover:bg-gray-800/30"><td class="px-5 py-3.5 font-semibold text-gray-200">Asif Khan</td><td class="px-4 py-3.5 text-gray-400">Prismatic Cell Batch #9922 · Vendor payment</td><td class="px-4 py-3.5 text-gray-400">Oct 04, 2026 · 15:42</td><td class="px-4 py-3.5"><span class="rounded border border-red-500/20 bg-red-500/10 px-2 py-1 text-[10px] text-red-400">Cash out</span></td><td class="px-4 py-3.5 text-right font-mono font-semibold text-red-400">Rs 3,400.00</td><td class="px-5 py-3.5 text-right font-mono text-red-400">−Rs 3,400.00</td></tr>
                                <tr class="hover:bg-gray-800/30"><td class="px-5 py-3.5 font-semibold text-gray-200">Zainab Malik</td><td class="px-4 py-3.5 text-gray-400">2 × 48V Type B server rack packs</td><td class="px-4 py-3.5 text-gray-400">Oct 04, 2026 · 14:15</td><td class="px-4 py-3.5"><span class="rounded border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-[10px] text-emerald-400">Cash in</span></td><td class="px-4 py-3.5 text-right font-mono font-semibold text-emerald-400">Rs 6,200.00</td><td class="px-5 py-3.5 text-right font-mono text-emerald-400">+Rs 6,200.00</td></tr>
                                <tr class="hover:bg-gray-800/30"><td class="px-5 py-3.5 font-semibold text-gray-200">Bilal Ahmed</td><td class="px-4 py-3.5 text-gray-400">1 × 24V heavy duty custom build</td><td class="px-4 py-3.5 text-gray-400">Oct 04, 2026 · 11:02</td><td class="px-4 py-3.5"><span class="rounded border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-[10px] text-emerald-400">Cash in</span></td><td class="px-4 py-3.5 text-right font-mono font-semibold text-emerald-400">Rs 2,450.00</td><td class="px-5 py-3.5 text-right font-mono text-emerald-400">+Rs 2,450.00</td></tr>
                                <tr class="hover:bg-gray-800/30"><td class="px-5 py-3.5 font-semibold text-gray-200">Asif Khan</td><td class="px-4 py-3.5 text-gray-400">JK-BMS control board batch order</td><td class="px-4 py-3.5 text-gray-400">Oct 03, 2026 · 16:50</td><td class="px-4 py-3.5"><span class="rounded border border-red-500/20 bg-red-500/10 px-2 py-1 text-[10px] text-red-400">Cash out</span></td><td class="px-4 py-3.5 text-right font-mono font-semibold text-red-400">Rs 1,150.00</td><td class="px-5 py-3.5 text-right font-mono text-red-400">−Rs 1,150.00</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-800 px-5 py-3 text-xs text-gray-500"><span>Showing 4 recent entries from salesperson ledgers</span><a href="#" class="font-medium text-amber-400 hover:text-amber-300">View all activity <i class="fas fa-arrow-right ml-1 text-[10px]"></i></a></div>
                </div>
            </div>

            <!-- SECTION 4: WORKFORCE & HR MODULE TAB -->
            <div id="tab-hr" class="space-y-8 hidden">
                <div class="flex items-center justify-between pb-2 border-b border-gray-800">
                    <div>
                        <h2 class="text-xl font-bold text-white">Workforce Compensation & Payroll Node</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Assembling Line Technicians, Quality Control Staff & Variable Account Commissions</p>
                    </div>
                    <button class="bg-gray-900 border border-gray-800 hover:bg-gray-800 text-xs font-semibold px-4 py-2 rounded-lg transition-all text-gray-200">
                        <i class="fas fa-calendar-alt mr-2 text-amber-500"></i>Lock Payroll Period
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div class="bg-gray-900 p-4 border border-gray-800 rounded-xl">
                        <span class="text-xs font-medium text-gray-400">Total Base Salary Component</span>
                        <p class="text-xl font-mono font-bold text-white mt-1">Rs 22,400.00</p>
                    </div>
                    <div class="bg-gray-900 p-4 border border-gray-800 rounded-xl">
                        <span class="text-xs font-medium text-gray-400">Overtime Yields (1.5x Premium)</span>
                        <p class="text-xl font-mono font-bold text-amber-400 mt-1">Rs 4,810.00</p>
                    </div>
                    <div class="bg-gray-900 p-4 border border-gray-800 rounded-xl">
                        <span class="text-xs font-medium text-gray-400">Accrued Representative Commissions</span>
                        <p class="text-xl font-mono font-bold text-emerald-400 mt-1">Rs 8,250.00</p>
                    </div>
                    <div class="bg-gray-900 p-4 border border-gray-800 rounded-xl">
                        <span class="text-xs font-medium text-gray-400">Gross Workforce Liability</span>
                        <p class="text-xl font-mono font-bold text-white mt-1">Rs 35,460.00</p>
                    </div>
                </div>

                <!-- HR Roster Table -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 overflow-hidden">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Active Staff Payroll Breakdown Matrix</h3>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-800">
                                    <th class="pb-3 font-semibold">Personnel Node</th>
                                    <th class="pb-3 font-semibold">Operational Function</th>
                                    <th class="pb-3 font-semibold text-right">Base Salary</th>
                                    <th class="pb-3 font-semibold text-right">Overtime Logs</th>
                                    <th class="pb-3 font-semibold text-right">Commissions Accrued</th>
                                    <th class="pb-3 font-semibold text-right">Gross Period Pay</th>
                                    <th class="pb-3 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/50">
                                <tr>
                                    <td class="py-3 font-bold text-gray-200">Haris Jamil</td>
                                    <td class="py-3 text-gray-400">Lead Pack Assembly Welder</td>
                                    <td class="py-3 text-right font-mono">Rs 3,200.00</td>
                                    <td class="py-3 text-right font-mono text-amber-400">22 hrs (Rs 792.00)</td>
                                    <td class="py-3 text-right font-mono text-gray-500">Rs 0.00</td>
                                    <td class="py-3 text-right font-mono font-bold text-white">Rs 3,992.00</td>
                                    <td class="py-3 text-center"><span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded text-[10px]">Pending Approval</span></td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-bold text-gray-200">Sana Mansoor</td>
                                    <td class="py-3 text-gray-400">BMS Diagnostics & Firmware Flasher</td>
                                    <td class="py-3 text-right font-mono">Rs 3,400.00</td>
                                    <td class="py-3 text-right font-mono text-amber-400">14 hrs (Rs 535.50)</td>
                                    <td class="py-3 text-right font-mono text-gray-500">Rs 0.00</td>
                                    <td class="py-3 text-right font-mono font-bold text-white">Rs 3,935.50</td>
                                    <td class="py-3 text-center"><span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded text-[10px]">Pending Approval</span></td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-bold text-gray-200">Asif Khan</td>
                                    <td class="py-3 text-gray-400">Salesperson (Senior Accounts Rep)</td>
                                    <td class="py-3 text-right font-mono">Rs 2,500.00</td>
                                    <td class="py-3 text-right font-mono text-gray-500">0 hrs (Rs 0.00)</td>
                                    <td class="py-3 text-right font-mono text-emerald-400">Rs 3,850.00</td>
                                    <td class="py-3 text-right font-mono font-bold text-white">Rs 6,350.00</td>
                                    <td class="py-3 text-center"><span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded text-[10px]">Verified & Locked</span></td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-bold text-gray-200">Zainab Malik</td>
                                    <td class="py-3 text-gray-400">Salesperson (Regional Accounts Rep)</td>
                                    <td class="py-3 text-right font-mono">Rs 2,500.00</td>
                                    <td class="py-3 text-right font-mono text-gray-500">0 hrs (Rs 0.00)</td>
                                    <td class="py-3 text-right font-mono text-emerald-400">Rs 2,900.00</td>
                                    <td class="py-3 text-right font-mono font-bold text-white">Rs 5,400.00</td>
                                    <td class="py-3 text-center"><span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded text-[10px]">Verified & Locked</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- SECTION 3: EXPENSE TRACKING MODULE TAB -->
            <div id="tab-expenses" class="space-y-8 hidden">
                <div class="flex items-center justify-between pb-2 border-b border-gray-800">
                    <div>
                        <h2 class="text-xl font-bold text-white">Multi-Channel Expense Ledger</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Track company and salesperson expenses by day or month</p>
                    </div>
                    <button class="bg-amber-500 text-gray-950 font-bold text-xs px-4 py-2 rounded-lg transition-all shadow-lg shadow-amber-500/10">
                        <i class="fas fa-plus mr-2"></i>File New Expense Record
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Expense Activity</h3>
                            <p class="mt-0.5 text-xs text-gray-500">Choose a date or month to review recorded spending</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="inline-flex rounded-lg border border-gray-800 bg-gray-900 p-1" role="group" aria-label="Expense period">
                                <button id="expense-period-day" type="button" aria-pressed="false" onclick="setExpensePeriod('day')" class="rounded-md px-3 py-1.5 text-xs font-medium text-gray-400 hover:text-white">Day</button>
                                <button id="expense-period-month" type="button" aria-pressed="true" onclick="setExpensePeriod('month')" class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-gray-950">Month</button>
                            </div>
                            <label class="sr-only" for="expense-day-picker">Expense date</label>
                            <input id="expense-day-picker" type="date" value="2026-10-04" onchange="filterExpenses()" class="hidden rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-200 color-scheme-dark">
                            <label class="sr-only" for="expense-month-picker">Expense month</label>
                            <input id="expense-month-picker" type="month" value="2026-10" onchange="filterExpenses()" class="rounded-lg border border-gray-800 bg-gray-900 px-3 py-2 text-xs text-gray-200 color-scheme-dark">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                            <p class="text-xs text-gray-400">Total Expenses</p>
                            <p id="expense-total" class="mt-2 text-2xl font-bold text-white">Rs 0.00</p>
                            <p id="expense-period-label" class="mt-1 text-[11px] text-gray-500">Selected month</p>
                        </div>
                        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                            <p class="text-xs text-gray-400">Expense Records</p>
                            <p id="expense-count" class="mt-2 text-2xl font-bold text-white">0</p>
                            <p class="mt-1 text-[11px] text-gray-500">Matching the selected period</p>
                        </div>
                        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                            <p class="text-xs text-gray-400">Average per Record</p>
                            <p id="expense-average" class="mt-2 text-2xl font-bold text-amber-400">Rs 0.00</p>
                            <p class="mt-1 text-[11px] text-gray-500">Total divided by record count</p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900">
                        <div class="flex items-center justify-between border-b border-gray-800 px-5 py-4">
                            <div><h4 class="text-sm font-semibold text-white">Expense Records</h4><p class="mt-0.5 text-xs text-gray-500">Company operating costs and salesperson support</p></div>
                            <span id="expense-match-count" class="text-xs text-gray-500">0 records</span>
                        </div>
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full min-w-[760px] border-collapse text-left text-xs">
                                <thead><tr class="border-b border-gray-800 text-gray-500"><th class="px-5 py-3 font-semibold">Date</th><th class="px-4 py-3 font-semibold">Expense</th><th class="px-4 py-3 font-semibold">Category</th><th class="px-4 py-3 font-semibold">Paid for</th><th class="px-5 py-3 text-right font-semibold">Amount</th></tr></thead>
                                <tbody id="expense-records" class="divide-y divide-gray-800/70">
                                    <tr data-expense-row data-expense-date="2026-10-04" data-expense-amount="3000" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 04, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Assembly consumables & PPE</td><td class="px-4 py-3.5 text-gray-400">Operations</td><td class="px-4 py-3.5 text-gray-400">Company</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 3,000.00</td></tr>
                                    <tr data-expense-row data-expense-date="2026-10-03" data-expense-amount="2100" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 03, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Lithium cell matching calibration rig</td><td class="px-4 py-3.5 text-gray-400">Equipment</td><td class="px-4 py-3.5 text-gray-400">Company</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 2,100.00</td></tr>
                                    <tr data-expense-row data-expense-date="2026-10-03" data-expense-amount="700" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 03, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Medical & health insurance</td><td class="px-4 py-3.5 text-gray-400">Personal support</td><td class="px-4 py-3.5 text-gray-400">Bilal Ahmed</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 700.00</td></tr>
                                    <tr data-expense-row data-expense-date="2026-10-02" data-expense-amount="1240" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 02, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Laser spot welding nitrogen refills</td><td class="px-4 py-3.5 text-gray-400">Operations</td><td class="px-4 py-3.5 text-gray-400">Company</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 1,240.00</td></tr>
                                    <tr data-expense-row data-expense-date="2026-10-02" data-expense-amount="950" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 02, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Domestic utility support</td><td class="px-4 py-3.5 text-gray-400">Personal support</td><td class="px-4 py-3.5 text-gray-400">Zainab Malik</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 950.00</td></tr>
                                    <tr data-expense-row data-expense-date="2026-10-01" data-expense-amount="6500" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 01, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Industrial facility lease & energy</td><td class="px-4 py-3.5 text-gray-400">Facilities</td><td class="px-4 py-3.5 text-gray-400">Company</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 6,500.00</td></tr>
                                    <tr data-expense-row data-expense-date="2026-10-01" data-expense-amount="1500" class="hover:bg-gray-800/30"><td class="px-5 py-3.5 text-gray-400">Oct 01, 2026</td><td class="px-4 py-3.5 font-medium text-gray-200">Residential rent stipend</td><td class="px-4 py-3.5 text-gray-400">Personal support</td><td class="px-4 py-3.5 text-gray-400">Asif Khan</td><td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-200">Rs 1,500.00</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p id="expense-empty-state" class="hidden px-5 py-8 text-center text-xs text-gray-500">No expenses recorded for this period.</p>
                    </div>
                </div>

                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Monthly Category Breakdown</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Column Left: Company Operational Expenses -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-800 mb-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-industry text-purple-400"></i>
                                <h3 class="font-bold text-white text-sm">Company Corporate Operational Expenses</h3>
                            </div>
                            <span class="text-xs text-purple-400 font-mono font-bold">Rs 12,840/mo</span>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <p class="font-bold text-gray-200">Industrial Facility Lease & Energy</p>
                                    <p class="text-gray-500 mt-0.5">Factory workshop floor high-voltage line access</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 6,500.00</span>
                            </div>
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <p class="font-bold text-gray-200">Laser Spot Welding Nitrogen Refills</p>
                                    <p class="text-gray-500 mt-0.5">Industrial gas supply contract for tab connections</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 1,240.00</span>
                            </div>
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <p class="font-bold text-gray-200">Lithium Cell Matching Calibration Rig</p>
                                    <p class="text-gray-500 mt-0.5">Internal resistance grading deck depreciation</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 2,100.00</span>
                            </div>
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <p class="font-bold text-gray-200">Assembly Consumables & PPE</p>
                                    <p class="text-gray-500 mt-0.5">Monthly production floor supplies</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 3,000.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column Right: Salesperson Personal Home Expenses -->
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-800 mb-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-home text-blue-400"></i>
                                <h3 class="font-bold text-white text-sm">Salesperson Personal Home Expenses Support</h3>
                            </div>
                            <span class="text-xs text-blue-400 font-mono font-bold">Rs 3,150/mo</span>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="font-bold text-gray-200">Asif Khan: Residential Rent Stipend</p>
                                        <span class="text-[9px] bg-gray-800 text-gray-400 px-1 rounded">Personal</span>
                                    </div>
                                    <p class="text-gray-500 mt-0.5">Approved executive household assistance benefit</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 1,500.00</span>
                            </div>
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="font-bold text-gray-200">Zainab Malik: Domestic Utility Support</p>
                                        <span class="text-[9px] bg-gray-800 text-gray-400 px-1 rounded">Personal</span>
                                    </div>
                                    <p class="text-gray-500 mt-0.5">Approved household domestic smart meter allowance</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 950.00</span>
                            </div>
                            <div class="bg-gray-950 p-3 rounded-xl border border-gray-850 flex justify-between items-center text-xs">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="font-bold text-gray-200">Bilal Ahmed: Medical & Health Insurance</p>
                                        <span class="text-[9px] bg-gray-800 text-gray-400 px-1 rounded">Personal</span>
                                    </div>
                                    <p class="text-gray-500 mt-0.5">Family tier healthcare coverage support program</p>
                                </div>
                                <span class="font-mono font-bold text-gray-300">Rs 700.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Simple Tab Switching Framework Script Logic -->
    <script>
        let expensePeriod = 'month';

        function setExpensePeriod(period) {
            expensePeriod = period;
            const isDay = period === 'day';
            const dayButton = document.getElementById('expense-period-day');
            const monthButton = document.getElementById('expense-period-month');

            dayButton.setAttribute('aria-pressed', String(isDay));
            monthButton.setAttribute('aria-pressed', String(!isDay));
            dayButton.classList.toggle('bg-amber-500', isDay);
            dayButton.classList.toggle('text-gray-950', isDay);
            dayButton.classList.toggle('font-semibold', isDay);
            dayButton.classList.toggle('text-gray-400', !isDay);
            monthButton.classList.toggle('bg-amber-500', !isDay);
            monthButton.classList.toggle('text-gray-950', !isDay);
            monthButton.classList.toggle('font-semibold', !isDay);
            monthButton.classList.toggle('text-gray-400', isDay);

            document.getElementById('expense-day-picker').classList.toggle('hidden', !isDay);
            document.getElementById('expense-month-picker').classList.toggle('hidden', isDay);
            filterExpenses();
        }

        function filterExpenses() {
            const isDay = expensePeriod === 'day';
            const selectedPeriod = document.getElementById(isDay ? 'expense-day-picker' : 'expense-month-picker').value;
            const rows = document.querySelectorAll('[data-expense-row]');
            let total = 0;
            let count = 0;

            rows.forEach((row) => {
                const rowDate = row.dataset.expenseDate;
                const matches = selectedPeriod && (isDay ? rowDate === selectedPeriod : rowDate.startsWith(selectedPeriod));
                row.classList.toggle('hidden', !matches);
                if (matches) {
                    total += Number(row.dataset.expenseAmount);
                    count += 1;
                }
            });

            const formattedTotal = `Rs ${total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('expense-total').textContent = formattedTotal;
            document.getElementById('expense-count').textContent = count;
            document.getElementById('expense-average').textContent = `Rs ${(count ? total / count : 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            document.getElementById('expense-period-label').textContent = isDay ? `Selected day: ${selectedPeriod || 'choose a date'}` : `Selected month: ${selectedPeriod || 'choose a month'}`;
            document.getElementById('expense-match-count').textContent = `${count} ${count === 1 ? 'record' : 'records'}`;
            document.getElementById('expense-empty-state').classList.toggle('hidden', count > 0);
        }

        filterExpenses();

        function switchTab(tabId) {
            // Hide all structural workspace section blocks
            document.getElementById('tab-dashboard').classList.add('hidden');
            document.getElementById('tab-inventory').classList.add('hidden');
            document.getElementById('tab-cash').classList.add('hidden');
            document.getElementById('tab-hr').classList.add('hidden');
            document.getElementById('tab-expenses').classList.add('hidden');

            // Reset selection style markers on all button groups
            resetButton('btn-dashboard');
            resetButton('btn-inventory');
            resetButton('btn-cash');
            resetButton('btn-hr');
            resetButton('btn-expenses');

            // Unveil requested tab item panel configuration
            document.getElementById('tab-' + tabId).classList.remove('hidden');

            // Set high-contrast highlight colors onto activated tab button node
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.remove('text-gray-400', 'hover:bg-gray-800', 'hover:text-white');
            activeBtn.classList.add('bg-amber-500', 'text-gray-950', 'shadow-lg', 'shadow-amber-500/10', 'font-semibold');
        }

        function resetButton(buttonId) {
            const btn = document.getElementById(buttonId);
            btn.classList.remove('bg-amber-500', 'text-gray-950', 'shadow-lg', 'shadow-amber-500/10', 'font-semibold');
            btn.classList.add('text-gray-400', 'hover:bg-gray-800', 'hover:text-white');
        }
    </script>
</body>
</html>
