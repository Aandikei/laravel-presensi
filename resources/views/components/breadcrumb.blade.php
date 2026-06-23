<nav class="flex mb-4" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 text-sm">
    @foreach($items as $i => $item)
      <li class="inline-flex items-center">
        @if($i > 0)
          <svg class="w-4 h-4 mx-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
          </svg>
        @endif
        @if(isset($item['url']))
          <a href="{{ $item['url'] }}" class="text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 transition-colors duration-150">
            {{ $item['label'] }}
          </a>
        @else
          <span class="text-gray-700 dark:text-gray-200 font-medium">{{ $item['label'] }}</span>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
