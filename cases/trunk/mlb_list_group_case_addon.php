<?php

			for ($i=0;$i<count($tab);$i++)
			{
				for ($j=0;$j<count($tab[$i]);$j++)
				{
					foreach(array_keys($tab[$i][$j]) as $value)
					{
						if($tab[$i][$j][$value]=='case_id')
						{
							$tab[$i][$j]['case_id']=$tab[$i][$j]['value'];
							$tab[$i][$j]["label"]=_NUM_CASE;
							$tab[$i][$j]["size"]="4";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="center";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["order"]='case_id';
						}
						if($tab[$i][$j][$value]=="case_label")
						{
							$tab[$i][$j]["label"]=_CASE_LABEL;
							$tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
							$tab[$i][$j]["size"]="15";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["order"]="case_label";
						}
						if($tab[$i][$j][$value]=="case_creation_date")
						{
							$tab[$i][$j]["label"]=_CASE_CREATION_DATE;
							$tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);;
							$tab[$i][$j]["size"]="5";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["order"]="case_creation_date";
						}
						if($tab[$i][$j][$value]=="case_typist")
						{
							$tab[$i][$j]["label"]=_CASE_TYPIST;
							$tab[$i][$j]['value'] = $request->show_string($tab[$i][$j]['value']);
							$tab[$i][$j]["size"]="25";
							$tab[$i][$j]["label_align"]="left";
							$tab[$i][$j]["align"]="left";
							$tab[$i][$j]["valign"]="bottom";
							$tab[$i][$j]["show"]=true;
							$tab[$i][$j]["value_export"] = $tab[$i][$j]['value'];
							$tab[$i][$j]["order"]="case_typist";
						}
					}
				}
			}
		
