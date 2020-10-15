<?php 
	use app\models\URLHelpers;
?>

<!-- Channels Nav Start -->
<nav class="channels-nav subpixel">

	<?php if ( $sidebar_items ) : ?>

	<ul>
		<?php

		$current = '';

		if ( isset($_GET['task']) AND !empty($_GET['task']) ) {
			$current = $_GET['task'];
		}

		foreach ($sidebar_items as $i => $item): 
			$step_id = $item['id'];
			$step = $item['step'];

			$class = '';
			if ( (!$i AND empty($current)) OR
				($current == strtolower($step))
			) {
				$class = 'class="current"';
			}
		?>
			
			<li>
		  		<a href="<?php echo URLHelpers::add_query_arg( 'task', strtolower($step) ); ?>" <?php echo $class; ?>>
			  		<span class="channel-icon <?php echo strtolower($step); ?>"></span> <?php echo $step; ?>
		  		</a>
		  	</li>

		<?php endforeach ?>

		<!--
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon media"></span> Media outlets
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon posts"></span> Posts
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon trending"></span> Trending now
	  		</a>
	  	</li>
	  	 <li>
	  		<a href="#">
		  		<span class="channel-icon guestposts"></span> Guestpost requests
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon facebook"></span> Facebook groups
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon facebook"></span> Facebook pages
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon twitter"></span> Tweets
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon twitter"></span> Twitter lists
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon reddit"></span> Subreddits
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon reddit"></span> Reddits
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon linkedin"></span> LinkedIn Groups
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon hacker-news"></span> Hacker News
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon quora"></span> Quora topics
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon quora"></span> Quora questions
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon yahoo"></span> Yahoo answers
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon google"></span> Google news
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon google-plus"></span> Google+ pages
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon google-plus"></span> Google+ communities
	  		</a>
	  	</li>
	  	<li>
	  		<a href="#">
		  		<span class="channel-icon pinterest"></span> Pinterest boards
	  		</a>
	  	</li>
	  	-->

  	<ul>

	<?php endif; ?>
	  		
</nav>
<!-- Channels Nav End -->