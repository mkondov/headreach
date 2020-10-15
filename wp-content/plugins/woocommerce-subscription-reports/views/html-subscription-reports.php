<?php

  if ( ! defined( 'ABSPATH' ) ) {
    exit;
  }

    $wsr_params = array();

    $wsr_params['domain']           = defined('WSR_DOMAIN') ? WSR_DOMAIN : '';
    $wsr_params['security']         = (!empty(WC_Subscription_Reporting::$wsr_nonce)) ? WC_Subscription_Reporting::$wsr_nonce : '';
    $wsr_params['currency_symbol']  = defined('WSR_CURRENCY_SYMBOL') ? WSR_CURRENCY_SYMBOL : '';
    $wsr_params['currency_pos']     = defined('WSR_CURRENCY_POS') ? WSR_CURRENCY_POS : '';
    $wsr_params['decimal_places']   = defined('WSR_DECIMAL_PLACES') ? WSR_DECIMAL_PLACES : 2;
    $wsr_params['thousand_sep']     = defined('WSR_THOUSAND_SEP') ? WSR_THOUSAND_SEP : ',';
    $wsr_params['decimal_sep']      = defined('WSR_DECIMAL_SEP') ? WSR_DECIMAL_SEP : '.';
    $wsr_params['img_up']           = defined('WSR_IMG_UP') ? WSR_IMG_UP : '';
    $wsr_params['img_down']         = defined('WSR_IMG_DOWN') ? WSR_IMG_DOWN : '';
    $wsr_params['num_format']       = defined('WSR_NUMBER_FORMAT') ? WSR_NUMBER_FORMAT : 0;

    $enddate       = current_time( 'Y-m-d' );
    $startdate     = date('Y-m-d', strtotime($enddate .' -30 day'));

    if ( ! wp_verify_nonce( $wsr_params['security'], 'wsr-security' ) ) {
        return;
    }

    $kpi_data = (defined('WSR_KPI_DATA')) ? json_decode(WSR_KPI_DATA,true) : array();

    global $wpdb;

    //check if the subscription snapshot table exists or not
    $table_name = "{$wpdb->prefix}wsr_orders";
    if(  $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        $order_count = $order_renewal_count = 0;

        $query = "SELECT COUNT(*) as order_count
                  FROM {$wpdb->prefix}posts
                  WHERE post_type IN ('shop_subscription')";

        $order_count = $wpdb->get_var($query);


        $query = "SELECT COUNT(pm.post_id)
                  FROM {$wpdb->prefix}postmeta AS pm
                    JOIN {$wpdb->prefix}posts AS p ON (pm.post_id = p.id 
                                      AND p.post_type = 'shop_order'
                                      AND p.post_parent = 0)
                  WHERE pm.meta_key IN ('_subscription_renewal', '_subscription_switch')
                    AND (pm.meta_value != '' OR pm.meta_value IS NOT NULL)
                    AND p.post_status != 'trash'";

        $order_renewal_count = $wpdb->get_var($query);

        $order_count += $order_renewal_count;

        if ($order_count >= 0) {

        ?>

          <div id="wsr_data_sync_msg" class="updated woocommerce-message wc-connect" style="margin-top:25%;text-align:center;border:0px;display:block;">
            <input id="wsr_data_sync_orders" type="hidden" value="<?php echo $order_count; ?>"> 
            <p style="max-width: 100%;"><?php _e( '<strong>WooCommerce Subscription Reports Data Sync Required</strong> &#8211; We just need to sync your subscription orders for faster reporting', $wsr_params['domain'] ); ?></p>
            <div class="submit" style="padding: 0;"> <a id="wsr_sync_link" href="<?php echo esc_url( add_query_arg( 'wsr_data_sync', 'true', admin_url( 'admin.php?page=wc-reports&tab=subscriptions' ) ) ); ?>" class="wc-update-now button-primary"><?php _e( 'Sync Now', $wsr_params['domain'] ); ?></a> </div> 
            <label id="wsr_data_sync_per">  </label> </p>
            
          </div>

        <?php

        if ( !empty($_GET) && !empty($_GET['wsr_data_sync']) ) {

          ?> 

          <script type="text/javascript">

              jQuery(function($){
                  var ocount = $('#wsr_data_sync_orders').val();

                  var ajax_count = 1;

                  $('#wsr_sync_link').attr('disabled',true).click(function(e) {
                      return false;
                  });

                  if ( ocount > 100 ) {
                      for ( i=0; i<ocount; ) {
                          ajax_count ++;
                          i = i+100;
                      }
                  }
                  else{
                      ajax_count = 1;
                  }

                  var subr_sync_data_req = function(num) {

                      var sfinal = ( num == ajax_count ) ? 1 : 0;

                      $.ajax({
                            type : 'POST',
                            url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=get_sub_stats' : ajaxurl + '?action=get_sub_stats', 
                            dataType:"text",
                            action: 'get_sub_stats',
                            data: {
                                    cmd: 'wsr_data_sync',
                                    part: num,
                                    sfinal: sfinal,
                                    params : <?php echo json_encode($wsr_params); ?>
                                },
                            success: function(response) {

                              if ( num<=ajax_count ) {

                                  if ( num == ajax_count ) {
                                      $("#wsr_sync_link").text( 'Sync Complete' );
                                      window.location = "<?php echo admin_url('admin.php?page=wc-reports&tab=subscriptions'); ?>";
                                  } else {
                                      $("#wsr_sync_link").text( ((num/ajax_count)*100).toFixed(2) + '% Completed' ); 
                                      num++;
                                      subr_sync_data_req(num); 
                                  }
                              }
                            }
                        });
                  }

                  subr_sync_data_req(1);
              });

          </script>

          <?php    
      }
        exit;

        }
    }

?>

<div id="wsr_beta" style="background-color:#F9F9F9;border-radius:5px;">

<!-- 
// ================================================
// Subscription KPI's
// ================================================
-->

<div id = "wsr_d3_chart"> </div>

<style type="text/css">
  .line {
      fill: none;
      /*stroke: #2693D5;*/
      /*stroke: #4DB7CB;*/
      stroke: #B2D6F6;
      stroke-width: 3px;
  }
  .circle {
      fill: white;
      stroke: #B2D6F6;
      stroke-width: 2px;
  }

  .d3-tip {
    line-height: 1;
    font-weight: bold;
    padding: 12px;
    background: rgba(0, 0, 0, 0.8);
    color: #fff;
    border-radius: 2px;
  }

  .area {
    /*fill: #E9F4FB;*/
    fill: #D4EDF8;
  }

  /* Creates a small triangle extender for the tooltip */
  .d3-tip:after {
    box-sizing: border-box;
    display: inline;
    font-size: 10px;
    width: 100%;
    line-height: 1;
    color: rgba(0, 0, 0, 0.8);
    content: "\25BC";
    position: absolute;
    text-align: center;
  }

  /* Style northward tooltips differently */
  .d3-tip.n:after {
    margin: -1px 0 0 0;
    top: 100%;
    left: 0;
  }


</style>

<script type="text/javascript">

var wsr_plot_graph = function(params) {

    var data = params.data;

    if(typeof (data) == 'undefined') {
        return;
    }

       var margin = {
           top: 3,
           right: 0,
           bottom: 5,
           left: 0
       },

       width = jQuery("#wsr_kpi_"+ params.kpi +"_graph").width(),
       height = jQuery("#wsr_kpi_"+ params.kpi +"_graph").height();

       var parseDate = d3.time.format(params.date_format).parse;

       var x = d3.time.scale()
           .range([0, width]);

       var y = d3.scale.linear()
           .range([height, 0]);

       var xAxis = d3.svg.axis()
           .scale(x)
           .orient("bottom");

       var yAxis = d3.svg.axis()
           .scale(y)
           .orient("left");

       var line = d3.svg.line()
           .interpolate("monotone") //for smoothness
           .x(function (d) {
            return x(d.date);
          })
           .y(function (d) {
            return y(d[params.kpi]);
       });

        var area = d3.svg.area()
            .interpolate("monotone") //for smoothness
            .x(function(d) { return x(d.date); })
            .y0(height)
            .y1(function(d) { return y(d[params.kpi]); });

      // function make_x_axis() {        
      //     return d3.svg.axis()
      //         .scale(x)
      //          .orient("bottom")
      //          .ticks(5)
      // }

      // function make_y_axis() {        
      //     return d3.svg.axis()
      //         .scale(y)
      //         .orient("left")
      //         .ticks(5)
      // }

       var tip = d3.tip()
           .attr('class', 'd3-tip')
           .offset([-10, 0])
           .html(function (d) {

              var formatted_val = d[params.kpi];

              if( params.tip_format != 'none' ) {
                  if( params.tip_format == '%' ) {
                      formatted_val = d[params.kpi] + '' + params.tip_format;
                  } else {
                      formatted_val = params.tip_format + '' + d[params.kpi];
                  }
              }
              

              return "<div>"+ d.formatted_date +" • " + formatted_val + "</div>";
            })

       var svg = d3.select("#wsr_kpi_"+ params.kpi +"_graph").append("svg")
           .attr("width", width + margin.left + margin.right)
           .attr("height", height + margin.top + margin.bottom)
           .append("g")
           .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
       
       svg.call(tip);

       data.forEach(function (d) {
           d.formatted_date = d.date;
           d.date = parseDate(d.date);
           d[params.kpi] = +d[params.kpi];
       });

       x.domain(d3.extent(data, function (d) {
           return d.date;
       }));
       y.domain(d3.extent(data, function (d) {
           return d[params.kpi];
       }));


        // svg.append("g")         
        //     .attr("class", "grid")
        //     .attr("transform", "translate(0," + height + ")")
        //     .call(make_x_axis()
        //         .tickSize(-height, 0, 0)
        //         .tickFormat("")
        //     )

        // svg.append("g")         
        //     .attr("class", "grid")
        //     .call(make_y_axis()
        //         .tickSize(-width, 0, 0)
        //         .tickFormat("")
        //     )

       // svg.append("g")
       //     .attr("class", "x axis")
       //     .attr("transform", "translate(0," + height + ")")
       //     .call(xAxis);

       // svg.append("g")
       //     .attr("class", "y axis")
       //     .call(yAxis)
       //     .append("text")
       //     .attr("transform", "rotate(-90)")
       //     .attr("y", 6)
       //     .attr("dy", ".71em")
       //     .style("text-anchor", "end")
       //     .text("Price ($)")

       svg.append("path")
           .datum(data)
           .attr("class", "area")
           .attr("d", area)

      svg.append("path")
          .datum(data)
          .attr("class", "line")  // with a darker color
          .attr("d", line);

       svg.selectAll(".circle")
           .data(data)
           .enter()
           .append("svg:circle")
           .attr("class", "circle")
           .attr("opacity", '0')
           .attr("cx", function (d, i) {
                return x(d.date);
            })
           .attr("cy", function (d, i) {
              return y(d[params.kpi]);
            })
           .attr("r", 1)
           .on('mouseover', function(d, i) {
              tip.show(d,i-1);
              tip.show(d,i+1);

              tip.show(d,i);
              d3.select(this)
                .attr("r", 2.7)
                .attr('opacity','1');
           })
           .on('mouseout', function(d,i) {
              tip.hide(d,i);
              d3.select(this)
                .attr("r", 1)
                .attr('opacity','0');
           })

         }

</script>


<div id ="wsr_kpi">
    <div class="row">
    <?php

        $css_class = array('first', 'second');
        $i = 0;
        $html = '';

        foreach ($kpi_data as $kpi => $data) {

            $html .= '<div id = "wsr_kpi_'. $kpi .'" align="center" class = "wsr_kpi_widget wsr_kpi_'. $css_class[$i] .'">
                          <div id="wsr_kpi_'. $kpi .'_data" class="wsr_kpi_data">
                              <span class = "wsr_kpi_price"> ' . $data['val'] . '</span>
                              <p class="wsr_kpi_text" style="color:inherit;"> '. $data['title'] .' </p>
                          </div>
                      </div>';

            $i++;
        }

        echo $html;

    ?>
  </div>
</div>

<div id="wsr_cumm_date" style="height:4.5em;margin-bottom:1.3em;background-color:white;text-align:center;">
  <div id="wsr_cumm_date_content" style="padding-top: 1.1em;">
          
        <select id ="wsr_smart_date_select" style="height:1.9em;font-size:1.2em;padding:0px;margin-top:-0.35em;" >
          <option value="" style="display:none;color: #333 !important;" selected> <?php _e('Select Date', $wsr_params['domain']); ?> </option>
          <option value="TODAY"> <?php _e('Today', $wsr_params['domain']); ?></option>
          <option value="YESTERDAY"> <?php _e('Yesterday', $wsr_params['domain']); ?></option>
          <option value="CURRENT_WEEK"> <?php _e('Current Week', $wsr_params['domain']); ?></option>
          <option value="LAST_WEEK"> <?php _e('Last Week', $wsr_params['domain']); ?></option>
          <option value="CURRENT_MONTH" selected > <?php _e('Current Month', $wsr_params['domain']); ?></option>
          <option value="LAST_MONTH"> <?php _e('Last Month', $wsr_params['domain']); ?></option>
          <option value="3_MONTHS"> <?php _e('3 Months', $wsr_params['domain']); ?></option>
          <option value="6_MONTHS"> <?php _e('6 Months', $wsr_params['domain']); ?></option>
          <option value="CURRENT_YEAR"> <?php _e('Current Year', $wsr_params['domain']); ?></option>
          <option value="LAST_YEAR"> <?php _e('Last Year', $wsr_params['domain']); ?></option>
          <option value="CUSTOM_DATE"> <?php _e('Custom Date', $wsr_params['domain']); ?></option>
        </select>

        <script type="text/javascript">
          if ( jQuery(window).width() <= 557 ) { //for mobile screens
              document.write('<br />');
          }

        </script>
        <span id="wsr_date_picker">
          <input type ="text" id="wsr_start_date" size="9" placeholder="yyyy-mm-dd" class = "wsr_dates from" >
          <span class = "wsr_cumm_date_label"> <?php _e('to', $wsr_params['domain']); ?> </span>
          <input type = "text" id="wsr_end_date" size="9" placeholder="yyyy-mm-dd" class = "wsr_dates to" >
          <input id = "wsr_custom_date_submit" type="button" class="button" value="<?php _e('Go', $wsr_params['domain']); ?>">
        </span>
  </div>
</div>

<!-- <br/> -->

<div id ="wsr_cumm_kpi"> 

  <fieldset id = "wsr_cumm_kpi_field">
   <!--  <legend>Last 30 Days</legend> -->
  </fieldset>  

</div>



<script type="text/javascript">

    var wsr_format_month = function(value) {
        value = ((value >= 0) && (value < 10)) ? '0' + value : value;
        return value;
    }

    var wsr_get_data = function(start_date, end_date) {

      start_date = (typeof start_date == 'undefined') ? "<?php echo $startdate; ?>" : start_date;
      end_date = (typeof end_date == 'undefined') ? "<?php echo $enddate; ?>" : end_date;

      jQuery(function($){
              $.ajax({
                    type : 'POST',
                    url: (ajaxurl.indexOf('?') !== -1) ? ajaxurl + '&action=get_sub_stats' : ajaxurl + '?action=get_sub_stats', 
                    dataType:"text",
                    async: false,
                    action: 'get_sub_stats',
                    data: {
                                cmd: 'cumm_kpi',
                                start_date : start_date,
                                end_date : end_date,
                                params : <?php echo json_encode($wsr_params); ?>
                        },
                    success: function(response) {
                        myJsonObj = (typeof (response) != 'undefined') ? $.parseJSON(response) : '';

                        if (typeof (myJsonObj) != '') {
                            var cumm_kpi_html = '';
                            var wsr_chart_data = new Array();

                            for (var kpi in myJsonObj['kpi']) {

                                if( kpi == 'top_sub' ) {
                                    continue;
                                }

                                kpi_formatted = kpi.replace(/[^a-zA-z0-9_-]/g,'');

                                wsr_chart_data[kpi_formatted] = new Array();
                                show_chart = false;

                               for(var i in myJsonObj['chart']['period']) {

                                  if( myJsonObj['chart'][kpi][i] > 0 ) {
                                      show_chart = true;
                                  }

                                  wsr_chart_data[kpi_formatted][i] = {'date':myJsonObj['chart']['period'][i].toString()};
                                  wsr_chart_data[kpi_formatted][i][kpi_formatted] = myJsonObj['chart'][kpi][i];
                               }

                               if( show_chart === false ) {
                                  delete wsr_chart_data[kpi_formatted];
                               }

                                
                                cumm_kpi_html += '<div id = "wsr_kpi_'+ kpi_formatted +'" class = "wsr_cumm_kpi_widget">'+
                                                    '<div id = "wsr_kpi_'+ kpi_formatted +'_data" style = "height:46.5%;">'+
                                                      myJsonObj['kpi'][kpi] +
                                                    '</div>' +
                                                    '<div id = "wsr_kpi_'+ kpi_formatted +'_graph" style = "height:45.8%;border-radius:5px;">'+
                                                    '</div>' +
                                                  '</div>';

                            }

                            if( typeof (myJsonObj['kpi']['top_sub']) != 'undefined' ) {
                                cumm_kpi_html += '<br/> <span id="wsr_top_sub_prod_container" style="padding-bottom:1.7em;"> <span id="wsr_top_sub_prod_title">Top Subscription Products</span> <table id="wsr_top_sub_prod" width="99.5%">'+ myJsonObj['kpi']['top_sub'] +'</table> </span>';  
                            }
                            
                            $('#wsr_cumm_kpi_field').html(cumm_kpi_html);
                            
                            //for plotting the graphs
                            for (var kpi in myJsonObj['kpi']) {

                                var params = {};
                                params.tip_format = myJsonObj['chart']['tip_format'][kpi];
                                params.date_format = (myJsonObj['meta']['date_format'] != "%H") ? myJsonObj['meta']['date_format'] : '%X';
                                params.kpi = kpi.replace(/[^a-zA-z0-9_-]/g,'');
                                params.kpi_nm = jQuery("#wsr_kpi_"+ params.kpi +"_data").children('h3').text();

                                if (typeof wsr_chart_data[params.kpi] == "undefined") {
                                   continue;
                                }
                                params.data = wsr_chart_data[params.kpi];
                                wsr_plot_graph(params);
                            }
                        }
                    }

                });
            });
          }

  jQuery(function($){

      jQuery(document).on('ready', function() {

              var max_date = "<?php echo $enddate; ?>";

              var dates = $( '.wsr_dates' ).datepicker({
                  changeMonth: true,
                  changeYear: true,
                  defaultDate: '',
                  dateFormat: 'yy-mm-dd',
                  numberOfMonths: 1,
                  maxDate: new Date( Date.parse(max_date)),
                  showButtonPanel: true,
                  showOn: 'focus',
                  buttonImageOnly: true,
                  onSelect: function( selectedDate ) {
                    var option = $( this ).is( '.from' ) ? 'minDate' : 'maxDate',
                      instance = $( this ).data( 'datepicker' ),
                      date = $.datepicker.parseDate( instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings );

                    dates.not( this ).datepicker( 'option', option, date );

                    $("#wsr_smart_date_select").val('CUSTOM_DATE');
                    
                  }
                });


              $("#wsr_smart_date_select").on('change',function(){

                  var smartdateValue = this.value;
                      get_data_flag = false;

                  if ( $(window).width() <= 557 ) { //for mobile screens
                      $("#wsr_custom_date").css({ "margin-top": "0.9em"});
                      $("#wsr_cumm_date1").css({ "height": "7em"});
                      $('#wsr_cumm_date1').css({"width" :"26.7em"});
                  } else {
                      $('#wsr_cumm_date1').css({"width" :"38em"});
                  }

                  $("#wsr_custom_date").css({ "display" : "block"});

                  var date = proSelectDate(smartdateValue, '<?php echo $enddate;?>'),
                      fromdate = new Date(date.fromDate),
                      todate = new Date(date.toDate);

                  $('#wsr_start_date').val(fromdate.getFullYear()+ '-' +(wsr_format_month(fromdate.getMonth()+1))+ '-' +wsr_format_month(fromdate.getDate()));
                  $('#wsr_end_date').val(todate.getFullYear()+ '-' +(wsr_format_month(todate.getMonth()+1))+ '-' +wsr_format_month(todate.getDate()));

                  if(smartdateValue != "CUSTOM_DATE"){
                    $('#wsr_custom_date_submit').trigger('click');
                  }

              });

              $('#wsr_custom_date_submit').on('click', function() { //code for handling 'Go' btn click
                  var start_date = $('#wsr_start_date').val(),
                      end_date = $('#wsr_end_date').val();

                  if( start_date != '' && end_date != '' ) {
                      wsr_get_data(start_date, end_date);  
                  }
              });

              $('#wsr_smart_date_select').val('CURRENT_MONTH').change();
      });

    });

</script>

</div>