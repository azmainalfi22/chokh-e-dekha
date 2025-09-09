
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- CSRF + user name (for optimistic comments) --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @auth
    <meta name="user-name" content="{{ auth()->user()->name }}">
  @endauth

  {{-- Theme boot (no flash) --}}
  <script>
    (() => {
      const saved = localStorage.getItem('theme'); // 'light' | 'dark' | null
      const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches;
      const shouldDark = saved ? (saved === 'dark') : !!prefersDark;
      if (shouldDark) document.documentElement.classList.add('dark');
      window.__theme = saved ?? (prefersDark ? 'dark' : 'light');
    })();
  </script>

  <title>@yield('title', 'Dashboard') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Include global theme tokens/utilities once for all pages --}}
  @include('partials._theme')

  {{-- Page-level style stacks --}}
  @stack('styles')

  <style>
    :root{
      /* page bg */
      --page-light: #fffaf5;
      --page-dark:  #0b0e12;
      --page-dark-grad-1: rgba(255,179,0,.06);
      --page-dark-grad-2: rgba(244,63,94,.07);

      /* elevation */
      --shadow-strong: 0 12px 30px rgba(15, 23, 42, .18), 0 4px 12px rgba(15,23,42,.12);
      --shadow-strong-hover: 0 18px 40px rgba(15, 23, 42, .22), 0 6px 16px rgba(15,23,42,.16);

      /* header */
      --header-bg-light: linear-gradient(90deg, rgba(254,243,199,.85), rgba(254,215,170,.85) 40%, rgba(254,205,211,.85));
      --header-bg-dark:  linear-gradient(90deg, rgba(30,27,22,.55), rgba(28,25,23,.55) 40%, rgba(31,26,23,.55));
      --header-ring: rgba(120, 53, 15, .15);

      /* nav text */
      --nav-text: #7c2d12;
      --nav-text-hover: #5a1e0a;
      --nav-text-active: #1f2937;

      --nav-text-dark: #fde68a;
      --nav-text-dark-hover: #fffbeb;
      --nav-text-dark-active: #fff;

      --ring-soft: 1px solid rgba(120, 53, 15, .15);
    }

body{
  min-height: 100vh;
  background:
    /* Bangladesh Civic City Background */
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 800' fill='none'%3E%3Cdefs%3E%3Cpattern id='dots' x='0' y='0' width='30' height='30' patternUnits='userSpaceOnUse'%3E%3Ccircle cx='15' cy='15' r='1' fill='%23059669' opacity='0.15'/%3E%3C/pattern%3E%3ClinearGradient id='skyGrad' x1='0%25' y1='0%25' x2='0%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2387ceeb;stop-opacity:0.1' /%3E%3Cstop offset='100%25' style='stop-color:%23fef3c7;stop-opacity:0.05' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='1200' height='800' fill='url(%23skyGrad)'/%3E%3Crect width='1200' height='800' fill='url(%23dots)'/%3E%3C!-- Buildings - Mixed Architecture --%3E%3Cg opacity='0.12'%3E%3C!-- Traditional Building with Dome --%3E%3Crect x='80' y='320' width='60' height='180' fill='%23dc2626'/%3E%3Crect x='85' y='315' width='50' height='10' fill='%23f59e0b'/%3E%3Ccircle cx='110' cy='315' r='15' fill='%2306b6d4'/%3E%3Cpath d='M95 315 Q110 300 125 315' fill='%2306b6d4'/%3E%3Crect x='90' y='350' width='8' height='12' fill='%23065f46'/%3E%3Crect x='102' y='350' width='8' height='12' fill='%23065f46'/%3E%3Crect x='114' y='350' width='8' height='12' fill='%23065f46'/%3E%3C!-- Modern Office Building --%3E%3Crect x='200' y='280' width='50' height='220' fill='%236366f1'/%3E%3Crect x='205' y='290' width='8' height='8' fill='%23fbbf24'/%3E%3Crect x='217' y='290' width='8' height='8' fill='%23fbbf24'/%3E%3Crect x='229' y='290' width='8' height='8' fill='%23fbbf24'/%3E%3Crect x='241' y='290' width='8' height='8' fill='%23fbbf24'/%3E%3Crect x='205' y='310' width='8' height='8' fill='%23fbbf24'/%3E%3Crect x='217' y='310' width='8' height='8' fill='%23fbbf24'/%3E%3Crect x='229' y='310' width='8' height='8' fill='%2365a30d'/%3E%3Crect x='241' y='310' width='8' height='8' fill='%23fbbf24'/%3E%3C!-- Colorful Apartment --%3E%3Crect x='320' y='350' width='70' height='150' fill='%2317a2b8'/%3E%3Crect x='325' y='360' width='12' height='15' fill='%23f59e0b'/%3E%3Crect x='345' y='360' width='12' height='15' fill='%23ef4444'/%3E%3Crect x='365' y='360' width='12' height='15' fill='%2310b981'/%3E%3Crect x='325' y='390' width='12' height='15' fill='%23a855f7'/%3E%3Crect x='345' y='390' width='12' height='15' fill='%23f59e0b'/%3E%3Crect x='365' y='390' width='12' height='15' fill='%23ef4444'/%3E%3C!-- Shop Buildings --%3E%3Crect x='450' y='420' width='80' height='80' fill='%2334d399'/%3E%3Crect x='460' y='430' width='15' height='20' fill='%236b7280'/%3E%3Crect x='480' y='430' width='40' height='30' fill='%23fbbf24'/%3E%3Ctext x='500' y='450' font-family='sans-serif' font-size='8' fill='%23dc2626'%3EShop%3C/text%3E%3C!-- Tea Stall --%3E%3Crect x='580' y='450' width='50' height='50' fill='%23f59e0b'/%3E%3Crect x='585' y='460' width='15' height='15' fill='%23dc2626'/%3E%3Crect x='605' y='460' width='15' height='15' fill='%23dc2626'/%3E%3Ctext x='605' y='480' font-family='sans-serif' font-size='6' fill='%23ffffff'%3ETea%3C/text%3E%3C!-- High-rise --%3E%3Crect x='750' y='250' width='45' height='250' fill='%2306b6d4'/%3E%3Crect x='755' y='260' width='6' height='6' fill='%23fbbf24'/%3E%3Crect x='765' y='260' width='6' height='6' fill='%23fbbf24'/%3E%3Crect x='775' y='260' width='6' height='6' fill='%2310b981'/%3E%3Crect x='785' y='260' width='6' height='6' fill='%23fbbf24'/%3E%3Crect x='755' y='280' width='6' height='6' fill='%2310b981'/%3E%3Crect x='765' y='280' width='6' height='6' fill='%23fbbf24'/%3E%3Crect x='775' y='280' width='6' height='6' fill='%23fbbf24'/%3E%3Crect x='785' y='280' width='6' height='6' fill='%2310b981'/%3E%3C!-- Mosque --%3E%3Crect x='900' y='380' width='60' height='120' fill='%23065f46'/%3E%3Ccircle cx='930' cy='375' r='12' fill='%23fbbf24'/%3E%3Cpath d='M918 375 Q930 360 942 375' fill='%23fbbf24'/%3E%3Crect x='920' y='400' width='20' height='30' fill='%23374151'/%3E%3Cpath d='M925 375 L925 360 M935 375 L935 360' stroke='%23fbbf24' stroke-width='2'/%3E%3C!-- Traditional House --%3E%3Crect x='1050' y='420' width='80' height='80' fill='%23dc2626'/%3E%3Cpath d='M1050 420 L1090 400 L1130 420 Z' fill='%2310b981'/%3E%3Crect x='1070' y='450' width='15' height='20' fill='%23374151'/%3E%3Crect x='1100' y='450' width='15' height='15' fill='%236366f1'/%3E%3C/g%3E%3C!-- Roads and Intersections --%3E%3Cg opacity='0.3'%3E%3C!-- Main Road --%3E%3Cpath d='M0 550 L1200 570' stroke='%236b7280' stroke-width='80' fill='none'/%3E%3C!-- Cross Road --%3E%3Cpath d='M400 400 L420 600' stroke='%236b7280' stroke-width='60' fill='none'/%3E%3C!-- Side Street --%3E%3Cpath d='M700 350 L750 600' stroke='%236b7280' stroke-width='40' fill='none'/%3E%3C!-- Road Markings --%3E%3Cpath d='M50 560 L100 562 M200 564 L250 566 M350 568 L400 570 M500 571 L550 572 M650 574 L700 575 M800 576 L850 577 M950 578 L1000 579 M1100 580 L1150 581' stroke='%23ffffff' stroke-width='4' fill='none' opacity='0.9'/%3E%3Cpath d='M405 420 L407 450 M409 480 L411 510 M413 540 L415 570' stroke='%23ffffff' stroke-width='3' fill='none' opacity='0.9'/%3E%3C/g%3E%3C!-- Colorful Buses --%3E%3Cg opacity='0.4'%3E%3C!-- Decorated Bus 1 --%3E%3Crect x='150' y='540' width='80' height='30' rx='5' fill='%23dc2626'/%3E%3Crect x='155' y='545' width='15' height='12' fill='%23fbbf24'/%3E%3Crect x='175' y='545' width='15' height='12' fill='%2310b981'/%3E%3Crect x='195' y='545' width='15' height='12' fill='%236366f1'/%3E%3Crect x='215' y='545' width='10' height='12' fill='%23a855f7'/%3E%3Ccircle cx='165' cy='580' r='8' fill='%23374151'/%3E%3Ccircle cx='215' cy='580' r='8' fill='%23374151'/%3E%3Cpath d='M150 540 Q190 535 230 540' stroke='%23fbbf24' stroke-width='3' fill='none'/%3E%3Cpath d='M155 555 Q175 552 195 555 Q215 552 225 555' stroke='%2310b981' stroke-width='2' fill='none'/%3E%3C!-- Bus 2 --%3E%3Crect x='500' y='555' width='70' height='25' rx='4' fill='%2310b981'/%3E%3Crect x='505' y='560' width='12' height='10' fill='%23fbbf24'/%3E%3Crect x='520' y='560' width='12' height='10' fill='%23ef4444'/%3E%3Crect x='535' y='560' width='12' height='10' fill='%236366f1'/%3E%3Crect x='550' y='560' width='12' height='10' fill='%23a855f7'/%3E%3Ccircle cx='515' cy='585' r='6' fill='%23374151'/%3E%3Ccircle cx='555' cy='585' r='6' fill='%23374151'/%3E%3Cpath d='M500 555 Q535 550 570 555' stroke='%23ef4444' stroke-width='2' fill='none'/%3E%3C/g%3E%3C!-- Rickshaws --%3E%3Cg opacity='0.35'%3E%3C!-- Rickshaw 1 --%3E%3Crect x='280' y='575' width='25' height='15' rx='2' fill='%23fbbf24'/%3E%3Ccircle cx='285' cy='595' r='5' fill='%23374151'/%3E%3Ccircle cx='300' cy='595' r='5' fill='%23374151'/%3E%3Cpath d='M275 575 Q285 570 295 575' stroke='%2310b981' stroke-width='2' fill='none'/%3E%3Cpath d='M270 580 L275 575' stroke='%23374151' stroke-width='2'/%3E%3C!-- Rickshaw 2 --%3E%3Crect x='650' y='520' width='25' height='15' rx='2' fill='%23ef4444'/%3E%3Ccircle cx='655' cy='540' r='5' fill='%23374151'/%3E%3Ccircle cx='670' cy='540' r='5' fill='%23374151'/%3E%3Cpath d='M645 525 Q655 520 665 525' stroke='%236366f1' stroke-width='2' fill='none'/%3E%3Cpath d='M640 530 L645 525' stroke='%23374151' stroke-width='2'/%3E%3C!-- Auto-rickshaw (CNG) --%3E%3Cpath d='M850 560 Q860 555 870 560 Q875 565 870 570 Q860 575 850 570 Q845 565 850 560' fill='%2310b981'/%3E%3Crect x='855' y='565' width='10' height='8' fill='%23fbbf24'/%3E%3Ccircle cx='852' cy='575' r='3' fill='%23374151'/%3E%3Ccircle cx='868' cy='575' r='3' fill='%23374151'/%3E%3C/g%3E%3C!-- Street Vendors and Stalls --%3E%3Cg opacity='0.25'%3E%3C!-- Fruit Cart --%3E%3Crect x='120' y='520' width='20' height='15' fill='%23f59e0b'/%3E%3Ccircle cx='125' cy='540' r='3' fill='%23374151'/%3E%3Ccircle cx='135' cy='540' r='3' fill='%23374151'/%3E%3Ccircle cx='125' cy='515' r='3' fill='%23ef4444'/%3E%3Ccircle cx='130' cy='515' r='3' fill='%2310b981'/%3E%3Ccircle cx='135' cy='515' r='3' fill='%23fbbf24'/%3E%3C!-- Street Food Cart --%3E%3Crect x='380' y='530' width='25' height='12' fill='%23dc2626'/%3E%3Ccircle cx='385' cy='547' r='3' fill='%23374151'/%3E%3Ccircle cx='400' cy='547' r='3' fill='%23374151'/%3E%3Crect x='385' y='525' width='15' height='5' fill='%23fbbf24'/%3E%3C!-- Newspaper Stand --%3E%3Crect x='680' y='500' width='15' height='20' fill='%236b7280'/%3E%3Crect x='682' y='505' width='11' height='3' fill='%23ffffff'/%3E%3Crect x='682' y='510' width='11' height='3' fill='%23ffffff'/%3E%3Crect x='682' y='515' width='11' height='3' fill='%23ffffff'/%3E%3C/g%3E%3C!-- People and Street Life --%3E%3Cg opacity='0.3'%3E%3C!-- Walking People --%3E%3Ccircle cx='100' cy='500' r='4' fill='%23374151'/%3E%3Cline x1='100' y1='504' x2='100' y2='525' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='100' y1='525' x2='95' y2='540' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='100' y1='525' x2='105' y2='530' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='100' y1='515' x2='90' y2='520' stroke='%23374151' stroke-width='2'/%3E%3C!-- Woman with colorful sari --%3E%3Ccircle cx='350' cy='490' r='4' fill='%23374151'/%3E%3Cline x1='350' y1='494' x2='350' y2='515' stroke='%23ef4444' stroke-width='3'/%3E%3Cline x1='350' y1='515' x2='345' y2='530' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='350' y1='515' x2='355' y2='530' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='350' y1='505' x2='340' y2='510' stroke='%23ef4444' stroke-width='2'/%3E%3C!-- Rickshaw Puller --%3E%3Ccircle cx='270' cy='570' r='4' fill='%23374151'/%3E%3Cline x1='270' y1='574' x2='270' y2='595' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='270' y1='595' x2='265' y2='610' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='270' y1='595' x2='275' y2='600' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='270' y1='585' x2='280' y2='580' stroke='%23374151' stroke-width='2'/%3E%3C!-- School Children --%3E%3Ccircle cx='550' cy='480' r='3' fill='%23374151'/%3E%3Cline x1='550' y1='483' x2='550' y2='500' stroke='%236366f1' stroke-width='2'/%3E%3Cline x1='550' y1='500' x2='545' y2='512' stroke='%23374151' stroke-width='1.5'/%3E%3Cline x1='550' y1='500' x2='555' y2='512' stroke='%23374151' stroke-width='1.5'/%3E%3Ccircle cx='565' cy='485' r='3' fill='%23374151'/%3E%3Cline x1='565' y1='488' x2='565' y2='505' stroke='%23ef4444' stroke-width='2'/%3E%3Cline x1='565' y1='505' x2='560' y2='517' stroke='%23374151' stroke-width='1.5'/%3E%3Cline x1='565' y1='505' x2='570' y2='517' stroke='%23374151' stroke-width='1.5'/%3E%3C!-- Street Vendor --%3E%3Ccircle cx='390' cy='520' r='4' fill='%23374151'/%3E%3Cline x1='390' y1='524' x2='390' y2='545' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='390' y1='545' x2='385' y2='560' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='390' y1='545' x2='395' y2='560' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='390' y1='535' x2='405' y2='535' stroke='%23374151' stroke-width='2'/%3E%3C!-- Police Officer --%3E%3Ccircle cx='720' cy='480' r='4' fill='%23374151'/%3E%3Cline x1='720' y1='484' x2='720' y2='505' stroke='%23059669' stroke-width='2'/%3E%3Cline x1='720' y1='505' x2='715' y2='520' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='720' y1='505' x2='725' y2='520' stroke='%23374151' stroke-width='2'/%3E%3Cline x1='720' y1='495' x2='730' y2='490' stroke='%23374151' stroke-width='2'/%3E%3C/g%3E%3C!-- Traffic Elements --%3E%3Cg opacity='0.35'%3E%3C!-- Traffic Light --%3E%3Crect x='395' y='470' width='10' height='30' rx='5' fill='%23374151'/%3E%3Ccircle cx='400' cy='477' r='3' fill='%23ef4444'/%3E%3Ccircle cx='400' cy='485' r='3' fill='%23fbbf24'/%3E%3Ccircle cx='400' cy='493' r='3' fill='%2310b981' opacity='0.8'/%3E%3C!-- Road Signs --%3E%3Crect x='600' y='450' width='15' height='10' fill='%236366f1'/%3E%3Ctext x='607' y='458' font-family='sans-serif' font-size='6' fill='%23ffffff'%3EBus%3C/text%3E%3Crect x='800' y='440' width='20' height='8' fill='%23dc2626'/%3E%3Ctext x='810' y='447' font-family='sans-serif' font-size='5' fill='%23ffffff'%3EStop%3C/text%3E%3C/g%3E%3C!-- Trees and Greenery --%3E%3Cg opacity='0.2'%3E%3C!-- Street Trees --%3E%3Ccircle cx='50' cy='480' r='15' fill='%2310b981'/%3E%3Crect x='48' y='495' width='4' height='20' fill='%23a16207'/%3E%3Ccircle cx='250' cy='460' r='12' fill='%2310b981'/%3E%3Crect x='248' y='472' width='4' height='18' fill='%23a16207'/%3E%3Ccircle cx='450' cy='470' r='10' fill='%2322c55e'/%3E%3Crect x='448' y='480' width='4' height='15' fill='%23a16207'/%3E%3Ccircle cx='900' cy='450' r='14' fill='%2310b981'/%3E%3Crect x='898' y='464' width='4' height='20' fill='%23a16207'/%3E%3Ccircle cx='1100' cy='480' r='12' fill='%2322c55e'/%3E%3Crect x='1098' y='492' width='4' height='18' fill='%23a16207'/%3E%3C/g%3E%3C!-- Birds and Atmosphere --%3E%3Cg opacity='0.25'%3E%3Cpath d='M200 150 Q205 145 210 150 Q205 155 200 150' stroke='%23374151' stroke-width='1' fill='none'/%3E%3Cpath d='M450 120 Q455 115 460 120 Q455 125 450 120' stroke='%23374151' stroke-width='1' fill='none'/%3E%3Cpath d='M700 140 Q705 135 710 140 Q705 145 700 140' stroke='%23374151' stroke-width='1' fill='none'/%3E%3Cpath d='M950 110 Q955 105 960 110 Q955 115 950 110' stroke='%23374151' stroke-width='1' fill='none'/%3E%3Cpath d='M1150 130 Q1155 125 1160 130 Q1155 135 1150 130' stroke='%23374151' stroke-width='1' fill='none'/%3E%3C!-- Small clouds --%3E%3Cpath d='M300 100 Q310 95 320 100 Q325 105 320 110 Q310 115 300 110 Q295 105 300 100' fill='%23ffffff' opacity='0.6'/%3E%3Cpath d='M800 80 Q810 75 820 80 Q825 85 820 90 Q810 95 800 90 Q795 85 800 80' fill='%23ffffff' opacity='0.5'/%3E%3C/g%3E%3C/svg%3E") no-repeat center center fixed,
    radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.15), transparent 40%),
    radial-gradient(1000px 300px at 110% 110%, rgba(34,197,94,.12), transparent 45%),
    radial-gradient(800px 200px at 50% 50%, rgba(99,102,241,.08), transparent 60%),
    linear-gradient(135deg, #fef7cd 0%, #ecfdf5 50%, #eff6ff 100%);
  background-size: cover, 100% 100%, 100% 100%, 100% 100%, 100% 100%;
  color: #0f172a;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.dark body{
  background:
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 800' fill='none'%3E%3Cdefs%3E%3Cpattern id='dots-dark' x='0' y='0' width='30' height='30' patternUnits='userSpaceOnUse'%3E%3Ccircle cx='15' cy='15' r='1' fill='%2322c55e' opacity='0.08'/%3E%3C/pattern%3E%3ClinearGradient id='skyGradDark' x1='0%25' y1='0%25' x2='0%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23111827;stop-opacity:0.8' /%3E%3Cstop offset='100%25' style='stop-color:%231f2937;stop-opacity:0.6' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='1200' height='800' fill='url(%23skyGradDark)'/%3E%3Crect width='1200' height='800' fill='url(%23dots-dark)'/%3E%3C!-- Buildings - Mixed Architecture --%3E%3Cg opacity='0.15'%3E%3C!-- Traditional Building with Dome --%3E%3Crect x='80' y='320' width='60' height='180' fill='%23fca5a5'/%3E%3Crect x='85' y='315' width='50' height='10' fill='%23fcd34d'/%3E%3Ccircle cx='110' cy='315' r='15' fill='%2367e8f9'/%3E%3Cpath d='M95 315 Q110 300 125 315' fill='%2367e8f9'/%3E%3Crect x='90' y='350' width='8' height='12' fill='%2334d399'/%3E%3Crect x='102' y='350' width='8' height='12' fill='%2334d399'/%3E%3Crect x='114' y='350' width='8' height='12' fill='%2334d399'/%3E%3C!-- Modern Office Building --%3E%3Crect x='200' y='280' width='50' height='220' fill='%238b5cf6'/%3E%3Crect x='205' y='290' width='8' height='8' fill='%23fcd34d'/%3E%3Crect x='217' y='290' width='8' height='8' fill='%23fcd34d'/%3E%3Crect x='229' y='290' width='8' height='8' fill='%23fcd34d'/%3E%3Crect x='241' y='290' width='8' height='8' fill='%23fcd34d'/%3E%3Crect x='205' y='310' width='8' height='8' fill='%23fcd34d'/%3E%3Crect x='217' y='310' width='8' height='8' fill='%23fcd34d'/%3E%3Crect x='229' y='310' width='8' height='8' fill='%2334d399'/%3E%3Crect x='241' y='310' width='8' height='8' fill='%23fcd34d'/%3E%3C!-- Colorful Apartment --%3E%3Crect x='320' y='350' width='70' height='150' fill='%2322d3ee'/%3E%3Crect x='325' y='360' width='12' height='15' fill='%23fcd34d'/%3E%3Crect x='345' y='360' width='12' height='15' fill='%23f87171'/%3E%3Crect x='365' y='360' width='12' height='15' fill='%2334d399'/%3E%3Crect x='325' y='390' width='12' height='15' fill='%23c084fc'/%3E%3Crect x='345' y='390' width='12' height='15' fill='%23fcd34d'/%3E%3Crect x='365' y='390' width='12' height='15' fill='%23f87171'/%3E%3C!-- Shop Buildings --%3E%3Crect x='450' y='420' width='80' height='80' fill='%2360efb7'/%3E%3Crect x='460' y='430' width='15' height='20' fill='%239ca3af'/%3E%3Crect x='480' y='430' width='40' height='30' fill='%23fcd34d'/%3E%3Ctext x='500' y='450' font-family='sans-serif' font-size='8' fill='%23dc2626'%3EShop%3C/text%3E%3C!-- Tea Stall --%3E%3Crect x='580' y='450' width='50' height='50' fill='%23fcd34d'/%3E%3Crect x='585' y='460' width='15' height='15' fill='%23dc2626'/%3E%3Crect x='605' y='460' width='15' height='15' fill='%23dc2626'/%3E%3Ctext x='605' y='480' font-family='sans-serif' font-size='6' fill='%23111827'%3ETea%3C/text%3E%3C!-- High-rise --%3E%3Crect x='750' y='250' width='45' height='250' fill='%2367e8f9'/%3E%3Crect x='755' y='260' width='6' height='6' fill='%23fcd34d'/%3E%3Crect x='765' y='260' width='6' height='6' fill='%23fcd34d'/%3E%3Crect x='775' y='260' width='6' height='6' fill='%2334d399'/%3E%3Crect x='785' y='260' width='6' height='6' fill='%23fcd34d'/%3E%3Crect x='755' y='280' width='6' height='6' fill='%2334d399'/%3E%3Crect x='765' y='280' width='6' height='6' fill='%23fcd34d'/%3E%3Crect x='775' y='280' width='6' height='6' fill='%23fcd34d'/%3E%3Crect x='785' y='280' width='6' height='6' fill='%2334d399'/%3E%3C!-- Mosque --%3E%3Crect x='900' y='380' width='60' height='120' fill='%2334d399'/%3E%3Ccircle cx='930' cy='375' r='12' fill='%23fcd34d'/%3E%3Cpath d='M918 375 Q930 360 942 375' fill='%23fcd34d'/%3E%3Crect x='920' y='400' width='20' height='30' fill='%236b7280'/%3E%3Cpath d='M925 375 L925 360 M935 375 L935 360' stroke='%23fcd34d' stroke-width='2'/%3E%3C!-- Traditional House --%3E%3Crect x='1050' y='420' width='80' height='80' fill='%23fca5a5'/%3E%3Cpath d='M1050 420 L1090 400 L1130 420 Z' fill='%2334d399'/%3E%3Crect x='1070' y='450' width='15' height='20' fill='%236b7280'/%3E%3Crect x='1100' y='450' width='15' height='15' fill='%238b5cf6'/%3E%3C/g%3E%3C!-- Roads and Intersections --%3E%3Cg opacity='0.25'%3E%3C!-- Main Road --%3E%3Cpath d='M0 550 L1200 570' stroke='%239ca3af' stroke-width='80' fill='none'/%3E%3C!-- Cross Road --%3E%3Cpath d='M400 400 L420 600' stroke='%239ca3af' stroke-width='60' fill='none'/%3E%3C!-- Side Street --%3E%3Cpath d='M700 350 L750 600' stroke='%239ca3af' stroke-width='40' fill='none'/%3E%3C!-- Road Markings --%3E%3Cpath d='M50 560 L100 562 M200 564 L250 566 M350 568 L400 570 M500 571 L550 572 M650 574 L700 575 M800 576 L850 577 M950 578 L1000 579 M1100 580 L1150 581' stroke='%23f3f4f6' stroke-width='4' fill='none' opacity='0.7'/%3E%3Cpath d='M405 420 L407 450 M409 480 L411 510 M413 540 L415 570' stroke='%23f3f4f6' stroke-width='3' fill='none' opacity='0.7'/%3E%3C/g%3E%3C!-- Colorful Buses --%3E%3Cg opacity='0.3'%3E%3C!-- Decorated Bus 1 --%3E%3Crect x='150' y='540' width='80' height='30' rx='5' fill='%23fca5a5'/%3E%3Crect x='155' y='545' width='15' height='12' fill='%23fcd34d'/%3E%3Crect x='175' y='545' width='15' height='12' fill='%2334d399'/%3E%3Crect x='195' y='545' width='15' height='12' fill='%238b5cf6'/%3E%3Crect x='215' y='545' width='10' height='12' fill='%23c084fc'/%3E%3Ccircle cx='165' cy='580' r='8' fill='%236b7280'/%3E%3Ccircle cx='215' cy='580' r='8' fill='%236b7280'/%3E%3Cpath d='M150 540 Q190 535 230 540' stroke='%23fcd34d' stroke-width='3' fill='none'/%3E%3Cpath d='M155 555 Q175 552 195 555 Q215 552 225 555' stroke='%2334d399' stroke-width='2' fill='none'/%3E%3C!-- Bus 2 --%3E%3Crect x='500' y='555' width='70' height='25' rx='4' fill='%2334d399'/%3E%3Crect x='505' y='560' width='12' height='10' fill='%23fcd34d'/%3E%3Crect x='520' y='560' width='12' height='10' fill='%23f87171'/%3E%3Crect x='535' y='560' width='12' height='10' fill='%238b5cf6'/%3E%3Crect x='550' y='560' width='12' height='10' fill='%23c084fc'/%3E%3Ccircle cx='515' cy='585' r='6' fill='%236b7280'/%3E%3Ccircle cx='555' cy='585' r='6' fill='%236b7280'/%3E%3Cpath d='M500 555 Q535 550 570 555' stroke='%23f87171' stroke-width='2' fill='none'/%3E%3C/g%3E%3C!-- Rickshaws --%3E%3Cg opacity='0.25'%3E%3C!-- Rickshaw 1 --%3E%3Crect x='280' y='575' width='25' height='15' rx='2' fill='%23fcd34d'/%3E%3Ccircle cx='285' cy='595' r='5' fill='%236b7280'/%3E%3Ccircle cx='300' cy='595' r='5' fill='%236b7280'/%3E%3Cpath d='M275 575 Q285 570 295 575' stroke='%2334d399' stroke-width='2' fill='none'/%3E%3Cpath d='M270 580 L275 575' stroke='%236b7280' stroke-width='2'/%3E%3C!-- Rickshaw 2 --%3E%3Crect x='650' y='520' width='25' height='15' rx='2' fill='%23f87171'/%3E%3Ccircle cx='655' cy='540' r='5' fill='%236b7280'/%3E%3Ccircle cx='670' cy='540' r='5' fill='%236b7280'/%3E%3Cpath d='M645 525 Q655 520 665 525' stroke='%238b5cf6' stroke-width='2' fill='none'/%3E%3Cpath d='M640 530 L645 525' stroke='%236b7280' stroke-width='2'/%3E%3C!-- Auto-rickshaw (CNG) --%3E%3Cpath d='M850 560 Q860 555 870 560 Q875 565 870 570 Q860 575 850 570 Q845 565 850 560' fill='%2334d399'/%3E%3Crect x='855' y='565' width='10' height='8' fill='%23fcd34d'/%3E%3Ccircle cx='852' cy='575' r='3' fill='%236b7280'/%3E%3Ccircle cx='868' cy='575' r='3' fill='%236b7280'/%3E%3C/g%3E%3C!-- Street Vendors and Stalls --%3E%3Cg opacity='0.2'%3E%3C!-- Fruit Cart --%3E%3Crect x='120' y='520' width='20' height='15' fill='%23fcd34d'/%3E%3Ccircle cx='125' cy='540' r='3' fill='%236b7280'/%3E%3Ccircle cx='135' cy='540' r='3' fill='%236b7280'/%3E%3Ccircle cx='125' cy='515' r='3' fill='%23f87171'/%3E%3Ccircle cx='130' cy='515' r='3' fill='%2334d399'/%3E%3Ccircle cx='135' cy='515' r='3' fill='%23fcd34d'/%3E%3C!-- Street Food Cart --%3E%3Crect x='380' y='530' width='25' height='12' fill='%23fca5a5'/%3E%3Ccircle cx='385' cy='547' r='3' fill='%236b7280'/%3E%3Ccircle cx='400' cy='547' r='3' fill='%236b7280'/%3E%3Crect x='385' y='525' width='15' height='5' fill='%23fcd34d'/%3E%3C!-- Newspaper Stand --%3E%3Crect x='680' y='500' width='15' height='20' fill='%239ca3af'/%3E%3Crect x='682' y='505' width='11' height='3' fill='%23e5e7eb'/%3E%3Crect x='682' y='510' width='11' height='3' fill='%23e5e7eb'/%3E%3Crect x='682' y='515' width='11' height='3' fill='%23e5e7eb'/%3E%3C/g%3E%3C!-- People and Street Life --%3E%3Cg opacity='0.2'%3E%3C!-- Walking People --%3E%3Ccircle cx='100' cy='500' r='4' fill='%23d1d5db'/%3E%3Cline x1='100' y1='504' x2='100' y2='525' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='100' y1='525' x2='95' y2='540' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='100' y1='525' x2='105' y2='530' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='100' y1='515' x2='90' y2='520' stroke='%23d1d5db' stroke-width='2'/%3E%3C!-- Woman with colorful sari --%3E%3Ccircle cx='350' cy='490' r='4' fill='%23d1d5db'/%3E%3Cline x1='350' y1='494' x2='350' y2='515' stroke='%23f87171' stroke-width='3'/%3E%3Cline x1='350' y1='515' x2='345' y2='530' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='350' y1='515' x2='355' y2='530' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='350' y1='505' x2='340' y2='510' stroke='%23f87171' stroke-width='2'/%3E%3C!-- Rickshaw Puller --%3E%3Ccircle cx='270' cy='570' r='4' fill='%23d1d5db'/%3E%3Cline x1='270' y1='574' x2='270' y2='595' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='270' y1='595' x2='265' y2='610' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='270' y1='595' x2='275' y2='600' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='270' y1='585' x2='280' y2='580' stroke='%23d1d5db' stroke-width='2'/%3E%3C!-- School Children --%3E%3Ccircle cx='550' cy='480' r='3' fill='%23d1d5db'/%3E%3Cline x1='550' y1='483' x2='550' y2='500' stroke='%238b5cf6' stroke-width='2'/%3E%3Cline x1='550' y1='500' x2='545' y2='512' stroke='%23d1d5db' stroke-width='1.5'/%3E%3Cline x1='550' y1='500' x2='555' y2='512' stroke='%23d1d5db' stroke-width='1.5'/%3E%3Ccircle cx='565' cy='485' r='3' fill='%23d1d5db'/%3E%3Cline x1='565' y1='488' x2='565' y2='505' stroke='%23f87171' stroke-width='2'/%3E%3Cline x1='565' y1='505' x2='560' y2='517' stroke='%23d1d5db' stroke-width='1.5'/%3E%3Cline x1='565' y1='505' x2='570' y2='517' stroke='%23d1d5db' stroke-width='1.5'/%3E%3C!-- Street Vendor --%3E%3Ccircle cx='390' cy='520' r='4' fill='%23d1d5db'/%3E%3Cline x1='390' y1='524' x2='390' y2='545' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='390' y1='545' x2='385' y2='560' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='390' y1='545' x2='395' y2='560' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='390' y1='535' x2='405' y2='535' stroke='%23d1d5db' stroke-width='2'/%3E%3C!-- Police Officer --%3E%3Ccircle cx='720' cy='480' r='4' fill='%23d1d5db'/%3E%3Cline x1='720' y1='484' x2='720' y2='505' stroke='%2334d399' stroke-width='2'/%3E%3Cline x1='720' y1='505' x2='715' y2='520' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='720' y1='505' x2='725' y2='520' stroke='%23d1d5db' stroke-width='2'/%3E%3Cline x1='720' y1='495' x2='730' y2='490' stroke='%23d1d5db' stroke-width='2'/%3E%3C/g%3E%3C!-- Traffic Elements --%3E%3Cg opacity='0.25'%3E%3C!-- Traffic Light --%3E%3Crect x='395' y='470' width='10' height='30' rx='5' fill='%236b7280'/%3E%3Ccircle cx='400' cy='477' r='3' fill='%23f87171'/%3E%3Ccircle cx='400' cy='485' r='3' fill='%23fcd34d'/%3E%3Ccircle cx='400' cy='493' r='3' fill='%2334d399' opacity='0.6'/%3E%3C!-- Road Signs --%3E%3Crect x='600' y='450' width='15' height='10' fill='%238b5cf6'/%3E%3Ctext x='607' y='458' font-family='sans-serif' font-size='6' fill='%23e5e7eb'%3EBus%3C/text%3E%3Crect x='800' y='440' width='20' height='8' fill='%23fca5a5'/%3E%3Ctext x='810' y='447' font-family='sans-serif' font-size='5' fill='%23111827'%3EStop%3C/text%3E%3C/g%3E%3C!-- Trees and Greenery --%3E%3Cg opacity='0.15'%3E%3C!-- Street Trees --%3E%3Ccircle cx='50' cy='480' r='15' fill='%2334d399'/%3E%3Crect x='48' y='495' width='4' height='20' fill='%23a16207'/%3E%3Ccircle cx='250' cy='460' r='12' fill='%2334d399'/%3E%3Crect x='248' y='472' width='4' height='18' fill='%23a16207'/%3E%3Ccircle cx='450' cy='470' r='10' fill='%2360efb7'/%3E%3Crect x='448' y='480' width='4' height='15' fill='%23a16207'/%3E%3Ccircle cx='900' cy='450' r='14' fill='%2334d399'/%3E%3Crect x='898' y='464' width='4' height='20' fill='%23a16207'/%3E%3Ccircle cx='1100' cy='480' r='12' fill='%2360efb7'/%3E%3Crect x='1098' y='492' width='4' height='18' fill='%23a16207'/%3E%3C/g%3E%3C!-- Birds and Atmosphere --%3E%3Cg opacity='0.15'%3E%3Cpath d='M200 150 Q205 145 210 150 Q205 155 200 150' stroke='%23d1d5db' stroke-width='1' fill='none'/%3E%3Cpath d='M450 120 Q455 115 460 120 Q455 125 450 120' stroke='%23d1d5db' stroke-width='1' fill='none'/%3E%3Cpath d='M700 140 Q705 135 710 140 Q705 145 700 140' stroke='%23d1d5db' stroke-width='1' fill='none'/%3E%3Cpath d='M950 110 Q955 105 960 110 Q955 115 950 110' stroke='%23d1d5db' stroke-width='1' fill='none'/%3E%3Cpath d='M1150 130 Q1155 125 1160 130 Q1155 135 1150 130' stroke='%23d1d5db' stroke-width='1' fill='none'/%3E%3C!-- Small clouds --%3E%3Cpath d='M300 100 Q310 95 320 100 Q325 105 320 110 Q310 115 300 110 Q295 105 300 100' fill='%236b7280' opacity='0.3'/%3E%3Cpath d='M800 80 Q810 75 820 80 Q825 85 820 90 Q810 95 800 90 Q795 85 800 80' fill='%236b7280' opacity='0.25'/%3E%3C/g%3E%3C/svg%3E"),
    radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.08), transparent 40%),
    radial-gradient(1000px 300px at 110% 110%, rgba(34,197,94,.06), transparent 45%),
    radial-gradient(800px 200px at 50% 50%, rgba(139,92,246,.05), transparent 60%),
    linear-gradient(135deg, #111827 0%, #1f2937 50%, #374151 100%);
  background-size: cover, 100% 100%, 100% 100%, 100% 100%, 100% 100%;
  color: #e5e7eb;
}

    @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }

    .btn {
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.55rem .9rem; border-radius:.9rem; line-height:1;
      transition: box-shadow .15s ease, transform .08s ease, background-color .15s ease, color .15s ease, opacity .15s ease;
      box-shadow: var(--shadow-strong); border: var(--ring-soft); backdrop-filter: blur(2px);
    }
    .btn:hover { box-shadow: var(--shadow-strong-hover); transform: translateY(-1px); }
    .btn:active { transform: translateY(0); box-shadow: var(--shadow-strong); }
    .btn-primary { background-image: linear-gradient(90deg, #d97706, #e11d48); color:#fff; border:none; text-shadow:0 1px 0 rgba(0,0,0,.12); }
    .btn-quiet   { background: rgba(255,255,255,.85); color: var(--nav-text); }
    .dark .btn-quiet { background: rgba(255,255,255,.08); color: var(--nav-text-dark); border-color: rgba(255,255,255,.06); }

    .header {
      position: fixed; inset-inline: 0; top: 0; z-index: 50;
      backdrop-filter: saturate(160%) blur(10px);
      -webkit-backdrop-filter: saturate(160%) blur(10px);
      border-bottom: 1px solid var(--header-ring);
      box-shadow: 0 10px 28px rgba(15,23,42,.20);
      background: var(--header-bg-light);
    }
    .dark .header { background: var(--header-bg-dark); border-bottom-color: rgba(255,255,255,.06); }

    .nav-link {
      position: relative;
      padding: .5rem .75rem;
      border-radius: .9rem;
      font-weight: 600;
      color: var(--nav-text);
      background: rgba(255,255,255,.85);
      border: 1px solid rgba(120,53,15,.12);
      transition: color .15s ease, background-color .15s ease, box-shadow .15s ease, transform .08s ease;
      box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }
    .nav-link:hover { color: var(--nav-text-hover); transform: translateY(-1px); box-shadow: 0 10px 20px rgba(0,0,0,.08); }
    .nav-link--active { background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.85)); color: var(--nav-text-active); }
    .dark .nav-link { color: var(--nav-text-dark); background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.06); }
    .dark .nav-link:hover { color: var(--nav-text-dark-hover); }
    .dark .nav-link--active { background: rgba(255,255,255,.14); color: var(--nav-text-dark-active); }

    .nav-link::after{
      content:""; position:absolute; left:.75rem; right:.75rem; bottom:.45rem;
      height:2px; border-radius:2px; background: currentColor; opacity:0; transform: scaleX(.6);
      transition: transform .18s ease, opacity .18s ease;
    }
    .nav-link:hover::after{ opacity:.45; transform: scaleX(1); }
    .nav-link--active::after{ opacity:.7; }

    .card-elevated { box-shadow: var(--shadow-strong); }
    .card-elevated:hover { box-shadow: var(--shadow-strong-hover); }

    .content-offset { padding-top: 5.0rem; } /* ~header height */

    /* Enhanced notification system styles */
    .notification-toast {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .notif-removing {
      animation: fadeOutScale 0.3s ease-out forwards;
    }
    .notif-badge {
      animation: bounceIn 0.6s ease-out;
    }
    .selected {
      background-color: rgba(59, 130, 246, 0.1) !important;
      border-color: rgba(59, 130, 246, 0.3) !important;
    }
    
    @keyframes fadeOutScale {
      from { opacity: 1; transform: scale(1); }
      to { opacity: 0; transform: scale(0.95); }
    }
    
    @keyframes bounceIn {
      0%, 20%, 53%, 80%, 100% { transform: scale(1); }
      40%, 43% { transform: scale(1.2); }
      70% { transform: scale(1.1); }
      90% { transform: scale(1.05); }
    }
  </style>
</head>

<body class="min-h-screen antialiased relative overflow-x-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 grainy">
  {{-- Background blobs (behind everything) --}}
  <div class="pointer-events-none absolute -top-24 -left-24 h-[26rem] w-[26rem] rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300 -z-10"></div>
  <div class="pointer-events-none absolute -bottom-28 -right-24 h-[30rem] w-[30rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300 -z-10"></div>

  {{-- Fetch notifications data safely --}}
  @php
    use Illuminate\Support\Facades\Schema;

    $layoutNotes = collect();
    $layoutUnread = 0;

    if (auth()->check() && Schema::hasTable('notifications')) {
      $u = auth()->user();
      $layoutUnread = $u->unreadNotifications()->count();
      $layoutNotes  = $u->notifications()->latest('created_at')->limit(12)->get();
    }
  @endphp

  {{-- Topbar --}}
  <header class="header">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
      {{-- Brand --}}
      <a href="{{ Route::has('dashboard') ? route('dashboard') : (Route::has('home') ? route('home') : url('/')) }}" class="flex items-center gap-2">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg>
        </span>
        <span class="font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-900 to-rose-700 dark:from-amber-300 dark:via-amber-200 dark:to-rose-200">
          {{ config('app.name', 'Chokh-e-Dekha') }}
        </span>
      </a>

      {{-- Nav --}}
      <nav class="flex items-center gap-2 text-sm flex-wrap">
        @if (Route::has('dashboard'))
          <a href="{{ route('dashboard') }}"
             class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link--active' : '' }}">Dashboard</a>
        @endif

        @if (Route::has('reports.my'))
          <a href="{{ route('reports.my') }}"
             class="nav-link {{ request()->routeIs('reports.my') ? 'nav-link--active' : '' }}">My Reports</a>
        @endif

        @if (Route::has('reports.index'))
          <a href="{{ route('reports.index') }}"
             class="nav-link {{ request()->routeIs('reports.index') ? 'nav-link--active' : '' }}">All Issues</a>
        @endif
      

        @stack('nav-actions')
      </nav>

      {{-- Right controls --}}
      <div class="flex items-center gap-2 flex-wrap">
        {{-- Theme toggle --}}
        <button id="themeToggle" type="button" class="btn btn-quiet" aria-label="Toggle dark mode">
          <svg class="h-4 w-4 hidden dark:block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 4V2m0 20v-2m8-8h2M2 12h2m13.657-6.343l1.414-1.414M4.929 19.071l1.414-1.414m0-11.314L4.93 4.929M19.071 19.071l-1.414-1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>
          </svg>
          <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/>
          </svg>
          <span class="text-sm font-medium dark:hidden">Dark</span>
          <span class="text-sm font-medium hidden dark:inline">Light</span>
        </button>

        {{-- Notifications bell --}}
        @auth
        <button id="notifToggle" type="button" class="btn btn-quiet relative" aria-label="Open notifications">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2a7 7 0 00-7 7v3.764l-1.553 3.106A1 1 0 004.342 17H19.66a1 1 0 00.895-1.47L19 12.764V9a7 7 0 00-7-7zm0 20a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
          </svg>
          @if($layoutUnread > 0)
            <span id="notifCount"
                  class="absolute -top-1 -right-1 inline-flex items-center justify-center h-4 min-w-[1rem] px-1
                         text-[10px] font-bold rounded-full bg-rose-600 text-white shadow">
              {{ $layoutUnread }}
            </span>
          @endif
        </button>
        @endauth

        {{-- Primary CTA --}}
        @if(Route::has('report.create'))
          <a href="{{ route('report.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
            New Report
          </a>
        @endif

        @stack('header-actions')

        {{-- Auth-aware user controls --}}
        @auth
          <div class="relative">
            <button id="userBtn" class="btn btn-quiet">
              <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7l4.5 5 4.5-5"/></svg>
            </button>
            <div id="userDrop" class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-white ring-1 ring-amber-900/10 shadow-2xl p-1 dark:bg-slate-900 dark:ring-white/10">
              @if (Route::has('profile.edit'))
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50 dark:hover:bg-white/10">Profile</a>
              @endif
              @if (Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-rose-50 text-rose-700 dark:hover:bg-rose-500/10 dark:text-rose-300">Logout</button>
                </form>
              @endif
            </div>
          </div>
        @endauth

        @guest
          @if (Route::has('login'))
            <a class="btn btn-quiet" href="{{ route('login') }}">Login</a>
          @endif
          @if (Route::has('register'))
            <a class="btn btn-primary" href="{{ route('register') }}">Register</a>
          @endif
        @endguest
      </div>
    </div>
  </header>

  {{-- Main (offset for fixed header) --}}
  <main class="mx-auto max-w-7xl px-4 py-6 relative z-10 content-offset">
    @if (session('status'))
      <div class="mb-4 rounded-xl px-4 py-3 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 card-elevated dark:bg-emerald-900/20 dark:text-emerald-100 dark:ring-emerald-800/40">
        {{ session('status') }}
      </div>
    @endif

    @hasSection('actions')
      <section class="mb-4 flex items-center gap-2 flex-wrap">
        @yield('actions')
      </section>
    @endif

    @yield('content')

    {{-- Notification bar (hidden until toggled) --}}
    @auth
      @include('partials.notifications-bar', [
        'notes'       => $layoutNotes,
        'unreadCount' => $layoutUnread,
        'title'       => 'My Alerts'
      ])
    @endauth
  </main>

  {{-- ===== Global helpers + Enhanced Notification System ===== --}}
  <script>
    // CSRF helper + AJAX wrapper
    window.csrf = () => (document.querySelector('meta[name="csrf-token"]')?.content || '');
    window.ajax = (url, options = {}) => {
      const headers = new Headers(options.headers || {});
      if (!headers.has('X-CSRF-TOKEN')) headers.set('X-CSRF-TOKEN', window.csrf());
      if (!headers.has('X-Requested-With')) headers.set('X-Requested-With', 'XMLHttpRequest');
      if (!headers.has('Accept')) headers.set('Accept', 'application/json');
      return fetch(url, { ...options, headers });
    };

    // Event delegation helpers
    window.closestData = (target, name) => target.closest(`[data-${name}]`);
    window.onClick = (selector, handler, opts = {capture:false}) => {
      document.addEventListener('click', (e) => {
        const el = e.target.closest(selector);
        if (el) handler(e, el);
      }, opts);
    };

    // Enhanced Notification Manager Class
    class NotificationManager {
      constructor() {
        this.pollingInterval = null;
        this.lastPollTime = null;
        this.isPolling = false;
        this.init();
      }

      init() {
        this.setupEventListeners();
        this.startPolling();
        this.setupKeyboardShortcuts();
      }

      setupEventListeners() {
        // Individual notification clearing
        document.addEventListener('click', (e) => {
          const clearBtn = e.target.closest('.notif-clear');
          if (clearBtn) {
            this.clearNotification(clearBtn.dataset.id, clearBtn);
          }
        });

        // Bulk clear all
        document.getElementById('notifClearAll')?.addEventListener('click', () => {
          this.clearAllNotifications();
        });

        // Mark single as read
        document.addEventListener('click', (e) => {
          const readBtn = e.target.closest('.notif-read');
          if (readBtn) {
            this.markAsRead(readBtn.dataset.id, readBtn);
          }
        });

        // Mark all as read
        document.getElementById('notifMarkAll')?.addEventListener('click', () => {
          this.markAllAsRead();
        });

        // Bulk selection (Ctrl+click)
        document.addEventListener('click', (e) => {
          const notifItem = e.target.closest('[data-notif-id]');
          if (notifItem && (e.ctrlKey || e.metaKey)) {
            this.toggleSelection(notifItem);
          }
        });
      }

      setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
          const notifBar = document.getElementById('notifBar');
          if (!notifBar || notifBar.classList.contains('hidden')) return;

          switch (e.key) {
            case 'Escape':
              this.closeNotifications();
              break;
            case 'a':
              if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                this.selectAll();
              }
              break;
            case 'r':
              if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                this.markAllAsRead();
              }
              break;
            case 'd':
              if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                this.clearSelected();
              }
              break;
          }
        });
      }

      async clearNotification(id, button) {
        const notifItem = button.closest('[data-notif-id]');
        const wasUnread = notifItem.querySelector('.unread-dot');

        this.setButtonLoading(button, true);

        try {
          const response = await fetch(`/notifications/${id}/clear`, {
            method: 'DELETE',
            headers: this.getHeaders(),
          });

          const data = await response.json();

          if (response.ok) {
            this.removeNotificationWithAnimation(notifItem);
            
            if (wasUnread) {
              this.updateBadgeCount(data.unread_count);
            }

            this.showToast('Notification removed', 'success');
            this.updateEmptyState();
          } else {
            throw new Error(data.message || 'Failed to clear notification');
          }
        } catch (error) {
          console.error('Error clearing notification:', error);
          this.showToast('Failed to remove notification', 'error');
          this.setButtonLoading(button, false);
        }
      }

      async clearAllNotifications() {
        if (!confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
          return;
        }

        const button = document.getElementById('notifClearAll');
        this.setButtonLoading(button, true, 'Clearing...');

        try {
          const response = await fetch('/notifications/clear-all', {
            method: 'DELETE',
            headers: this.getHeaders(),
          });

          const data = await response.json();

          if (response.ok) {
            const notifications = document.querySelectorAll('[data-notif-id]');
            notifications.forEach((notif, index) => {
              setTimeout(() => {
                this.removeNotificationWithAnimation(notif);
              }, index * 50);
            });

            this.updateBadgeCount(0);
            this.showToast('Cleared all notifications', 'success');
            
            setTimeout(() => {
              this.updateEmptyState();
              button.style.display = 'none';
            }, notifications.length * 50 + 300);
          } else {
            throw new Error(data.message || 'Failed to clear all notifications');
          }
        } catch (error) {
          console.error('Error clearing all notifications:', error);
          this.showToast('Failed to clear all notifications', 'error');
          this.setButtonLoading(button, false, 'Clear all');
        }
      }

      async markAsRead(id, button) {
        const notifItem = button.closest('[data-notif-id]');
        const unreadDot = notifItem.querySelector('.unread-dot');

        if (!unreadDot) return;

        this.setButtonLoading(button, true);

        try {
          const response = await fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: this.getHeaders(),
          });

          const data = await response.json();

          if (response.ok) {
            unreadDot.remove();
            button.remove();

            this.updateBadgeCount(data.unread_count);
            this.showToast('Marked as read', 'success');
          } else {
            throw new Error(data.message || 'Failed to mark as read');
          }
        } catch (error) {
          console.error('Error marking as read:', error);
          this.showToast('Failed to mark as read', 'error');
          this.setButtonLoading(button, false);
        }
      }

      async markAllAsRead() {
        const button = document.getElementById('notifMarkAll');
        this.setButtonLoading(button, true, 'Marking...');

        try {
          const response = await fetch('/notifications/read-all', {
            method: 'POST',
            headers: this.getHeaders(),
          });

          const data = await response.json();

          if (response.ok) {
            document.querySelectorAll('.unread-dot').forEach(dot => dot.remove());
            document.querySelectorAll('.notif-read').forEach(btn => btn.remove());

            this.updateBadgeCount(0);
            this.showToast(data.message, 'success');
            button.style.display = 'none';
          } else {
            throw new Error(data.message || 'Failed to mark all as read');
          }
        } catch (error) {
          console.error('Error marking all as read:', error);
          this.showToast('Failed to mark all as read', 'error');
          this.setButtonLoading(button, false, 'Mark all read');
        }
      }

      startPolling() {
        if (this.isPolling) return;

        this.isPolling = true;
        this.lastPollTime = new Date().toISOString();

        this.pollingInterval = setInterval(async () => {
          await this.pollForNewNotifications();
        }, 30000);
      }

      async pollForNewNotifications() {
        try {
          const response = await fetch(`/notifications/poll?last_poll=${this.lastPollTime}`, {
            headers: this.getHeaders()
          });

          if (response.ok) {
            const data = await response.json();
            
            if (data.has_new) {
              this.handleNewNotifications(data.notifications);
              this.updateBadgeCount(data.unread_count);
            }

            this.lastPollTime = data.last_poll;
          }
        } catch (error) {
          console.error('Error polling for notifications:', error);
        }
      }

      handleNewNotifications(notifications) {
        notifications.forEach(notification => {
          // Show desktop notification if permission granted
          if (Notification.permission === 'granted') {
            this.showDesktopNotification(notification);
          }
        });

        if (notifications.length > 0) {
          const message = notifications.length === 1 
            ? 'New notification received'
            : `${notifications.length} new notifications received`;
          this.showToast(message, 'info');
        }
      }

      showDesktopNotification(notification) {
        const data = notification.data || {};
        const title = data.title || 'New Notification';
        const body = data.message || 'You have a new notification';
        
        new Notification(title, {
          body: body,
          icon: '/favicon.ico',
          badge: '/favicon.ico',
          tag: notification.id
        });
      }

      requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
          Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
              this.showToast('Desktop notifications enabled', 'success');
            }
          });
        }
      }

      toggleSelection(notifItem) {
        notifItem.classList.toggle('selected');
        this.updateBulkActions();
      }

      selectAll() {
        const notifications = document.querySelectorAll('[data-notif-id]');
        notifications.forEach(item => item.classList.add('selected'));
        this.updateBulkActions();
      }

      clearSelection() {
        const selected = document.querySelectorAll('[data-notif-id].selected');
        selected.forEach(item => item.classList.remove('selected'));
        this.updateBulkActions();
      }

      clearSelected() {
        this.bulkClearSelected();
      }

      updateBulkActions() {
        const selectedCount = document.querySelectorAll('[data-notif-id].selected').length;
        let bulkActions = document.getElementById('bulkActions');
        
        if (selectedCount > 0) {
          if (!bulkActions) {
            bulkActions = this.createBulkActionsBar();
            document.getElementById('notifBar').appendChild(bulkActions);
          }
          bulkActions.querySelector('.bulk-count').textContent = selectedCount;
          bulkActions.style.display = 'flex';
        } else if (bulkActions) {
          bulkActions.style.display = 'none';
        }
      }

      createBulkActionsBar() {
        const bulkBar = document.createElement('div');
        bulkBar.id = 'bulkActions';
        bulkBar.className = 'absolute bottom-0 left-0 right-0 bg-blue-500/90 text-white p-3 flex items-center justify-between';
        bulkBar.innerHTML = `
          <div class="flex items-center gap-2">
            <span class="bulk-count font-semibold">0</span>
            <span>selected</span>
          </div>
          <div class="flex items-center gap-2">
            <button id="bulkClear" class="px-3 py-1 bg-red-500/80 rounded hover:bg-red-500">Clear</button>
            <button id="bulkCancel" class="px-3 py-1 bg-white/20 rounded hover:bg-white/30">Cancel</button>
          </div>
        `;

        bulkBar.querySelector('#bulkClear').addEventListener('click', () => this.bulkClearSelected());
        bulkBar.querySelector('#bulkCancel').addEventListener('click', () => this.clearSelection());

        return bulkBar;
      }

      async bulkClearSelected() {
        const selected = document.querySelectorAll('[data-notif-id].selected');
        if (selected.length === 0) return;

        if (!confirm(`Clear ${selected.length} selected notifications?`)) return;

        try {
          const response = await fetch('/notifications/bulk-clear', {
            method: 'DELETE',
            headers: this.getHeaders(),
            body: JSON.stringify({
              notification_ids: Array.from(selected).map(item => item.dataset.notifId)
            })
          });

          const data = await response.json();

          if (response.ok) {
            selected.forEach((item, index) => {
              setTimeout(() => {
                this.removeNotificationWithAnimation(item);
              }, index * 30);
            });

            this.updateBadgeCount(data.unread_count);
            this.showToast(data.message, 'success');
            this.clearSelection();
          } else {
            throw new Error(data.message || 'Failed to clear selected notifications');
          }
        } catch (error) {
          console.error('Error clearing selected notifications:', error);
          this.showToast('Failed to clear selected notifications', 'error');
        }
      }

      // Utility methods
      removeNotificationWithAnimation(element) {
        element.classList.add('notif-removing');
        setTimeout(() => {
          element.remove();
          this.updateEmptyState();
        }, 300);
      }

      updateEmptyState() {
        const notifList = document.getElementById('notifList');
        const notifItems = notifList?.querySelectorAll('[data-notif-id]');
        
        if (!notifItems || notifItems.length === 0) {
          if (notifList && !document.getElementById('notifEmpty')) {
            notifList.innerHTML = `
              <div id="notifEmpty" class="p-8 text-center">
                <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100/80 dark:bg-white/10 mb-4">
                  <svg class="h-8 w-8 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a2 2 0 00-2 2v1.1A7.001 7.001 0 005 12v4l-2 2v1h18v-1l-2-2v-4a7.001 7.001 0 00-5-6.9V4a2 2 0 00-2-2z"/>
                  </svg>
                </div>
                <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">All caught up!</div>
                <div class="text-xs text-slate-500 dark:text-slate-500">No new notifications to show.</div>
              </div>
            `;
          }
          
          // Hide action buttons
          document.getElementById('notifClearAll')?.style.setProperty('display', 'none');
          document.getElementById('notifMarkAll')?.style.setProperty('display', 'none');
        }
      }

      updateBadgeCount(newCount) {
        const badges = document.querySelectorAll('#notifCount');
        
        badges.forEach(badge => {
          if (newCount > 0) {
            const badgeText = newCount > 99 ? '99+' : newCount.toString();
            badge.textContent = badgeText;
            badge.classList.add('notif-badge');
            setTimeout(() => badge.classList.remove('notif-badge'), 600);
          } else {
            badge.remove();
          }
        });

        // Hide mark all button if no unread notifications
        if (newCount === 0) {
          document.getElementById('notifMarkAll')?.style.setProperty('display', 'none');
        }
      }

      setButtonLoading(button, loading, text = null) {
        if (loading) {
          button.disabled = true;
          button.dataset.originalText = button.innerHTML;
          button.innerHTML = `
            <svg class="h-3 w-3 animate-spin mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2v4m0 12v4m10-10h-4M6 12H2"/>
            </svg>
            ${text || 'Loading...'}
          `;
        } else {
          button.disabled = false;
          button.innerHTML = button.dataset.originalText || text || button.innerHTML;
        }
      }

      showToast(message, type = 'success') {
        // Remove existing toasts
        document.querySelectorAll('.notification-toast').forEach(t => t.remove());
        
        const toast = document.createElement('div');
        toast.className = 'notification-toast fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 max-w-sm';
        toast.textContent = message;
        
        // Style based on type
        switch (type) {
          case 'success':
            toast.className += ' bg-emerald-500 text-white';
            break;
          case 'error':
            toast.className += ' bg-red-500 text-white';
            break;
          case 'warning':
            toast.className += ' bg-amber-500 text-white';
            break;
          case 'info':
            toast.className += ' bg-blue-500 text-white';
            break;
        }
        
        document.body.appendChild(toast);
        
        // Auto remove
        setTimeout(() => {
          toast.style.opacity = '0';
          toast.style.transform = 'translateX(100%)';
          setTimeout(() => toast.remove(), 300);
        }, 3000);
      }

      closeNotifications() {
        const bar = document.getElementById('notifBar');
        if (bar) {
          bar.classList.add('hidden');
        }
      }

      getHeaders() {
        return {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        };
      }
    }

    // Basic layout functions
    const initUserDropdown = () => {
      const btn  = document.getElementById('userBtn');
      const drop = document.getElementById('userDrop');
      if (!btn || !drop) return;
      btn.addEventListener('click', () => drop.classList.toggle('hidden'));
      document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !drop.contains(e.target)) drop.classList.add('hidden');
      }, { capture: true });
    };

    const initThemeToggle = () => {
      const btn = document.getElementById('themeToggle');
      if (!btn) return;
      const withTrans = (fn)=>{ const el=document.documentElement; el.style.transition='background-color .25s,color .25s'; fn(); setTimeout(()=>el.style.transition='',300); };
      btn.addEventListener('click', () => {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        withTrans(() => {
          if (isDark) { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); }
          else        { html.classList.add('dark');    localStorage.setItem('theme', 'dark'); }
        });
      });
      try {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', (e) => {
          if (!localStorage.getItem('theme')) {
            const html = document.documentElement;
            e.matches ? html.classList.add('dark') : html.classList.remove('dark');
          }
        });
      } catch {}
    };

    const initNotificationToggle = () => {
      document.getElementById('notifToggle')?.addEventListener('click', () => {
        const bar = document.getElementById('notifBar');
        if (!bar) return;
        bar.classList.toggle('hidden');
      });

      // Ensure the bar starts hidden
      const bar = document.getElementById('notifBar');
      if (bar && !bar.dataset.booted) {
        bar.classList.add('hidden');
        bar.dataset.booted = '1';
      }
    };

    // Initialize everything
    const initLayout = () => { 
      initUserDropdown(); 
      initThemeToggle(); 
      initNotificationToggle();
      
      // Initialize notification manager
      if (document.getElementById('notifBar')) {
        window.notificationManager = new NotificationManager();
        
        // Request notification permission on first interaction
        document.addEventListener('click', () => {
          window.notificationManager.requestNotificationPermission();
        }, { once: true });
      }
    };
    
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initLayout, {once:true});
    } else {
      initLayout();
    }
    window.addEventListener?.('turbo:load', initLayout);

    // Export NotificationManager for use in other scripts
    window.NotificationManager = NotificationManager;
  </script>

  {{-- Page-level scripts --}}
  @stack('scripts')
</body>
</html>