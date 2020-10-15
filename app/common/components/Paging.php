<?php

namespace app\common\components;

class Paging {

	public function displayPaging( $count, $per_page = 20 ) {
		
		if ( $count < $per_page ) {
			return false;
		}

		$html = '<ul class="pagination" role="navigation" aria-label="Pagination">';
		$html .= $this->getLinks( $count, $per_page );
		$html .= '</ul>';

		echo $html;
	}

	public function getLinks( $count, $per_page ) {


		$LINKS_PER_STEP = 5;

		$page = 1;
		if  ( isset($_GET['page']) AND intval($_GET['page']) ) {
			$page = $_GET['page'];
		}

		$lastPage = ceil( $count / $per_page );


		if ( $page == 1 ) {
	    	$result = '<li class="pagination-previous disabled">Previous <span class="show-for-sr">page</span></li>';
	    } else {
	    	$result = '<li class="pagination-previous"><a href="'. URLHelpers::add_query_arg( 'page', $page - 1 ) .'">Previous <span class="show-for-sr">page</span></a></li>';
	    }

	    if ( $page == $lastPage ) {
	    	$end_paging = '<li class="pagination-next disabled">Next <span class="show-for-sr">page</span></li>';
	    } else {
	    	$end_paging = '<li class="pagination-next"><a href="'. URLHelpers::add_query_arg( 'page', $page + 1 ) .'" aria-label="Next page">Next <span class="show-for-sr">page</span></a></li>';
	    }

		// $URL = '?page=';
		// if ($page>1)
		// $result = '<form action="' . $URL . '1" method="POST" style="display:inline"><input type="submit" value="&nbsp;|&lt;&nbsp;"></form>&nbsp;' .
		// '<form action="' . $URL . ($page-1) . '" method="POST" style="display:inline"><input type="submit" value="&nbsp;&lt;&nbsp;"></form>';
		// else $result = '<input type="button" value="&nbsp;|&lt;&nbsp;" disabled>&nbsp;<input type="button" value="&nbsp;&lt;&nbsp;" disabled>';

		// $result .= '&nbsp;&nbsp;' . $page . '&nbsp;&nbsp;';

		// if ($page<$lastPage)
		// $result .= '<form action="' . $URL . ($page+1) . '" method="POST" style="display:inline"><input type="submit" value="&nbsp;&gt;&nbsp;"></form>&nbsp;' .
		// '<form action="' . $URL . $lastPage . '" method="POST" style="display:inline"><input type="submit" value="&nbsp;&gt;|&nbsp;"></form>';
		// else $result .= '<input type="button" value="&nbsp;&gt;&nbsp;" disabled>&nbsp;<input type="button" value="&nbsp;&gt;|&nbsp;" disabled>';
		// $result .= "<br>";

		$lastp1 = 1;
		$lastp2 = $page;
		$p1 = 1;
		$p2 = $page;
		$c1 = $LINKS_PER_STEP + 1;
		$c2 = $LINKS_PER_STEP + 1;
		$s1 = '';
		$s2 = '';
		$step = 1;
	  
		while (true) {

			if ($c1>=$c2) {
				$s1 .= $this->paginationGap($lastp1,$p1) . $this->paginationLink($p1,$page);
				$lastp1 = $p1;
				$p1 += $step;
				$c1--;
			} else {
				$s2 = $this->paginationLink($p2,$page) . $this->paginationGap($p2,$lastp2) . $s2;
				$lastp2 = $p2;
				$p2 -= $step;
				$c2--;
			}

			if ($c2==0) {
				$step *= 10;
				$p1 += $step - 1;         // Round UP to nearest multiple of $step
				$p1 -= ($p1 % $step);
				$p2 -= ($p2 % $step);   // Round DOWN to nearest multiple of $step
				$c1 = $LINKS_PER_STEP;
				$c2 = $LINKS_PER_STEP;
			}

			if ($p1>$p2) {
				$result .= $s1 . $this->paginationGap($lastp1,$lastp2) . $s2;
				if ( ($lastp2 > $page) || ($page >= $lastPage) ) return $result . $end_paging;
				$lastp1 = $page;
				$lastp2 = $lastPage;
				$p1 = $page + 1;
				$p2 = $lastPage;
				$c1 = $LINKS_PER_STEP;
				$c2 = $LINKS_PER_STEP + 1;
				$s1 = '';
				$s2 = '';
				$step = 1;
			}

		}
	}

	public function paginationLink($p, $page) {
		if ( $p == $page ) {
			$html .= '<li class="current"><span class="show-for-sr">You\'re on page</span> '. $page .'</li>';
		} else {
			$html .= '<li><a href="'. URLHelpers::add_query_arg( 'page', $p ) .'">' . $p . '</a></li>';
		}

		return $html;
	}

	public function paginationGap($p1, $p2) {
		$x = $p2-$p1;
		if ($x==0) return '';
		if ($x==1) return ' ';
		if ($x<=10) return '<span class="hide-for-small-only"> . </span>';
		if ($x<=100) return '<span class="hide-for-small-only"> .. <span>';
		return '<span class="hide-for-small-only"> ... </span>';
	}

}