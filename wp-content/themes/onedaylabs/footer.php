<div class="container-fluid">		
            <footer role="contentinfo">
			
				<div id="inner-footer" class="clearfix">
		          <div class="container">
                      <div id="widget-footer" class="clearfix row">      
                        <?php dynamic_sidebar('footer1'); ?>
                        <?php dynamic_sidebar('footer2'); ?>
                        <?php dynamic_sidebar('footer3'); ?>
                        <?php dynamic_sidebar('footer4'); ?>
                        
                            <div style="clear:both"></div>
    
                        <?php dynamic_sidebar('footer_bottom'); ?>
    
                        </div>
				
				</div> <!-- end #inner-footer -->
				</div>
			</footer> <!-- end footer -->
	</div>
	</div> <!-- end #container -->
				
		<!--[if lt IE 7 ]>
  			<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
  			<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
		<![endif]-->
		
		<?php wp_footer(); // js scripts are inserted using this function ?>

		<!-- remove this for production -->

		
<script type=”text/javascript” src=”http://maps.google.com/maps/api/js?sensor=true”> </script>
	</body>

</html>