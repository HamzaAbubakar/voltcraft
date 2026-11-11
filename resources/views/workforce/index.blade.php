@extends('layouts.dashboard')

@section('content')
<div class="space-y-8">
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
@endsection
