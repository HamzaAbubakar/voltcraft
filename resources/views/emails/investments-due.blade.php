@component('mail::message')
# Investment Repayments Due

The following {{ count($investments) }} investment(s) are due within 3 days or past their return date:

@foreach ($investments as $investment)
- **{{ $investment['investor_name'] }}** — Due {{ $investment['maturity_date'] }} ({{ $investment['days_until_maturity'] < 0 ? 'Overdue by '.abs($investment['days_until_maturity']).' day(s)' : 'Due in '.$investment['days_until_maturity'].' day(s)' }})
@endforeach

Please review and record settlements in the Investments module.

{{ config('app.name', 'Voltcraft ERP') }}
@endcomponent