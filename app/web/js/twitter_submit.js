jQuery(document)
		.ready(
				function(jQuery) {

					
					jQuery("#keyword-button").click(
									function(event) {
										event.preventDefault();
										
										keyword = jQuery('#input-keyword').val();
										json_request = {
												"keyword" : keyword,
											};

											json_request = JSON.stringify(json_request);
											console.log(json_request);
											
											jQuery('#keyword-wait').show();
											jQuery('#keyword-results-table').hide();

										var jqxhr = jQuery
												.ajax(
														{
															type : "POST",
															url : "index.php?r=ajax/submittwitter",
															accepts : "application/json",
															async : true, // do not block browser
															contentType : "application/json; charset=utf-8",
															data : json_request
														})
												.done(
														function(response) {
															
															jQuery('#keyword-wait').hide();

															console.log(response);

															try {
																var jsonResponse = JSON
																		.parse(response);

																console.log(jsonResponse);
																console
																		.log(jsonResponse.code);

																if (jsonResponse.code == 200) {
																	
																	results = jsonResponse.results;
																	
																	console.log(results.length);
																	for(i=0;i<results.length;i++){
																		
																		company_social = results[i].company_social.join("</br>");
																		person_social = results[i].person_social.join("</br>");
																		contact_info = results[i].contact_info.join("</br>");
																		
																		html = "<td>"+results[i].company+"</td>";
																		html += "<td>"+company_social+"</td>";
																		html += "<td>"+results[i].title+"</td>";
																		html += "<td>"+results[i].full_name+"</td>";
																		html += "<td>"+results[i].email+"</td>";
																		html += "<td>"+contact_info+"</td>";
																		html += "<td>"+person_social+"</td>";
																		jQuery('#keyword-results-table tr:last')
																		.after('<tr>'+html+'</tr>');
																		console.log(html);
																	}
																	jQuery('#keyword-results-table').show();
																	

																	//window.location.href = "http://cozy.bg/жилище/преглед/"+jsonResponse.offer_id;
																	//console.log(jsonResponse.offer_id);

																} else {
																	throw "Моля, опитайте отново по-късно.";
																}
															} catch (e) {
																//displayStatusMessage(false,
																//		e);
															}

														}).fail(function(response) {
													//console.log(response);
												}).always(function(response) {
													//console.log(response);
													//jQuery("#btn-offer-publish").removeClass("disabled");
												});
									});


				});