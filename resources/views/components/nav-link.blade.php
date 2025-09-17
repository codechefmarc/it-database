@props(['active' => FALSE])

<a class="{{ $active ? 'bg-gray-900 text-white' : 'text-gray-900 hover:bg-gray-700 hover:text-white' }} block rounded-md px-3 py-2 text-sm font-medium"
  {{ $attributes }}
  aria-current={{ request()->is('/') ? 'page' : 'false' }}
>{{ $slot }}</a>
