<?php
/** Funciones de presentacion compartidas por la Mesa de Servicios. */

function badgeEstado(string $s): string
{
    $c = ['Abierto' => 'bg-amber-100 text-amber-800', 'En proceso' => 'bg-blue-100 text-blue-800',
          'En espera' => 'bg-slate-200 text-slate-700', 'Resuelto' => 'bg-emerald-100 text-emerald-800',
          'Cerrado' => 'bg-slate-100 text-slate-500'][$s] ?? 'bg-slate-100 text-slate-600';
    return '<span class="' . $c . ' text-[10px] font-bold px-2 py-0.5 rounded-full">' . e($s) . '</span>';
}

function badgePrioridad(string $p): string
{
    $c = ['Baja' => 'text-slate-500', 'Media' => 'text-sky-600', 'Alta' => 'text-amber-600', 'Crítica' => 'text-rose-600'][$p] ?? 'text-slate-500';
    return '<span class="' . $c . ' text-xs font-semibold"><i class="fa-solid fa-flag text-[10px] mr-1"></i>' . e($p) . '</span>';
}

