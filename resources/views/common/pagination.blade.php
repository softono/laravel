<nav aria-label="Page navigation example">
  <ul class="pagination">

    <li class="page-item page-pre {{ $page == 1 ? 'disabled' : '' }}">
      @if($page ==1)
      <a class="page-link" data-page="1" href="javascript:void(0);" aria-label="Previous">
        <i class="bx bx-chevron-left"></i>
      </a>
      @else
      <a class="page-link" href="javascript:void(0);" data-page="{{ $page-1 }}" aria-label="Previous">
        <i class="bx bx-chevron-left"></i>
      </a>
      @endIf
    </li>

    @If($page >4)
    <li class="page-item"><a class="page-link" data-page="1" href="javascript:void(0);">1</a></li>
    <li class="page-item"><a class="page-link" href="javascript:void(0);">...</a></li>
    @endIf

    @If($page-3>0)
    <li class="page-item"><a class="page-link" data-page="{{$page-3}}" href="javascript:void(0);">{{$page-3}}</a></li>
    @endIf
    @If($page-2>0)
    <li class="page-item"><a class="page-link" data-page="{{$page-2}}" href="javascript:void(0);">{{$page-2}}</a></li>
    @endIf
    @If($page-1>0)
    <li class="page-item"><a class="page-link" data-page="{{$page-1}}" href="javascript:void(0);">{{$page-1}}</a></li>
    @endIf

    <li class="page-item active"><a class="page-link" data-page="{{$page}}" href="javascript:void(0);">{{$page}}</a></li>

    @If($page+1<=$lastPage)
    <li class="page-item"><a class="page-link" data-page="{{$page+1}}" href="javascript:void(0);">{{$page+1}}</a></li>
    @endIf
    @If($page+2<=$lastPage)
    <li class="page-item"><a class="page-link" data-page="{{$page+2}}" href="javascript:void(0);">{{$page+2}}</a></li>
    @endIf
    @If($page+3<=$lastPage)
    <li class="page-item"><a class="page-link" data-page="{{$page+3}}" href="javascript:void(0);">{{$page+3}}</a></li>
    @endIf

    @If($lastPage > $page+3)
      @If($page<$lastPage)
      <li class="page-item"><a class="page-link" href="javascript:void(0);">...</a></li>
      <li class="page-item"><a class="page-link last" data-page="{{$lastPage}}" href="javascript:void(0);">{{$lastPage}}</a></li>
      @endIf
    @endIf
    <li class="page-item page-next {{ $page == $lastPage ? 'disabled' : '' }}">
      @If($page == $lastPage)
      <a class="page-link" data-page="{{$lastPage}}" aria-label="Next" href="javascript:void(0);">
        <i class="bx bx-chevron-right"></i>
      </a>
      @else
      <a class="page-link" aria-label="Next" data-page="{{ $page+1 }}" href="javascript:void(0);">
        <i class="bx bx-chevron-right"></i>
      </a>
      @endif
    </li>
  </ul>
</nav>
