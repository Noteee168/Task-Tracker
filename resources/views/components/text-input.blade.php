@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 bg-slate-50 focus:bg-white text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-2xs text-sm transition']) }}>
