function add_this_tags(action_script, ui_script)
{
	//if(res_id == '' || coll_id == '')
	//{
	//	if(console)
	//	{
	//		console.log('Error add_this_tag :: coll_id or res_id is empty');
	//	}
	//}
	//Allons chercher l'info du formulaire en l etat...
	var content = $('tag_userform').value;
	if(action_script)
	{

		new Ajax.Request(action_script,
		{
			method:'post',
			parameters:
			{
				p_input_value : content,
				//p_res_id : res_id,
				//p_coll_id : coll_id
				
			},
		    onSuccess: function(answer){
			eval("response = "+answer.responseText);
				//alert(answer.responseText);
				if(response.status == 0 )
				{
					//load_tags(ui_script,res_id,coll_id)
					load_tags(ui_script)
				}
				else
				{
					if(console)
					{
						console.log('Erreur Ajax');
					}
				}
			},
		    onFailure: function(){ alert('Something went wrong...'); },
		});
		
	}
	else
	{
		if(console)
		{
			console.log('Error delete_this_tag::no script defined');
		}
	}
}




function delete_this_tag(action_script,tag_label, ui_script)
{
	if(tag_label == '')
	{
		if(console)
		{
			console.log('Error delete_this_tag :: tag_label');
		}
	}
	if(action_script)
	{	
		new Ajax.Request(action_script,
		{
			method:'post',
			parameters:
			{
				p_tag_label : tag_label,			
			},
		    onSuccess: function(answer){
			eval("response = "+answer.responseText);
				//alert(answer.responseText);
				if(response.status == 0 )
				{
					load_tags(ui_script)
				}
				else
				{
					if(console)
					{
						console.log('Erreur Ajax');
					}
				}
			},
		    onFailure: function(){ alert('Something went wrong...'); },
		});
		
	}
	else
	{
		if(console)
		{
			console.log('Error delete_this_tag::no script defined');
		}
	}
}

//Affiche l'ensemble des tags dans la div désirée
//function load_tags(path_script,res_id,coll_id)
function load_tags(path_script)
{
	if(path_script)
	{		
		new Ajax.Request(path_script,
		{
			method:'post',
			parameters:
			{
				p_res_id : '10',
			},
		    onSuccess: function(answer){
			eval("response = "+answer.responseText);
				if(response.status == 0 )
				{
					//On lance la fonction d'affichage des tags.
					var inner = response.value;
					var mydiv = $("tag_displayed");
					mydiv.innerHTML = inner;
					var myform = $('tag_userform');
					myform.value = "";
				}
				else
				{
					if(console)
					{
						console.log('Erreur Ajax');
					}
				}
			},
		    onFailure: function(){ alert('Something went wrong...'); },
		});
		
	}
	else
	{
		if(console)
		{
			console.log('Error delete_this_tag::no script defined');
		}
	}
}
