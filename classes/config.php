<?php

set_time_limit(0);
error_reporting(0);

define('hostDB',"localhost");
define('userDB',"root");
define('parolaDB',"");
define('bazaDeDate',"ftbf");

global $conn,$TABLES,$FIELDS;

$conn = mysqli_connect(hostDB,userDB,parolaDB,bazaDeDate) or die('Cred ca nu ma pot conecta la baza de date...');

function encode($var1 , $var2){
	return md5($var1.sha1($var2));
}

$FIELDS = array('users' => array('id' => 'id',
								 'user' => 'user',
								 'parola' => 'parola',
								 'rand' => 'random',
								 'email' => 'email',
								 'confirmResetPass' => 'confirmReset',
								 'randConfirmPass' => 'randConfirm',
								 'rank' => 'rank',
								 'adresa' => 'adresa',
								 'cod' => 'cod',
								 'nume' => 'nume',
								 'actiuni' => 'actiuni',
								 'dataInscr' => 'data_inscriere',
								 'cont' => 'cont',
								 'lastLogin' => 'lastLogin',
								 'lastLogout' => 'lastLogout',
								 'idLastBilet' => 'idLastBilet',
								 'castigPosibil' => 'castig_posibil',
								 'biletulMare' => 'biletul_valoros',
								 'posturi' => 'posturi',
								 'semnatura' => 'semnatura'
								 ),
				'stiri' => array('id' => 'id_stire',
								 'data' => 'data',
								 'titlu' => 'titlu',
								 'body' => 'continut',
								 'img' => 'img',
								 'votanti' => 'votanti',
								 'media' => 'media',
								 'dataMare' => 'dataMare',
								 'views' => 'vizionari',
								 'sursa' => 'sursa',
								 'prop' => 'proposer'
								),
				'facils' => array('id' => 'id',
								  'lastNNews' => 'lastNNews',
								  'maxBet' => 'val_max_castig',
								  'maxUser' => 'val_max_user',
								  'maxLoggedUsers' => 'max_logged_users',
								  'maxLoggedDate' => 'max_logged_date'
								 ),
				'tags' => array('id' => 'id_tag',
								'tag' => 'tag',
								'hits' => 'hits',
								'id_obj' => 'id_obj',
								'obj' => 'obj'
								),
				'userTags' => array('id' => 'id_tag',
									'tag' => 'tag',
									'obj' => 'obj',
									'user' => 'id_user',
									'hits' => 'hits'
									),
				'allTags' => array('tag' => 'tag',
								   'hits' => 'hits'),
				'newsUsers' => array('email' => 'email',
									 'data' => 'data'
									 ),
				'propuneri' => array('id' => 'id',
									 'prop' => 'proposer',
									 'type' => 'ce_propune',
									 'titlu' => 'titlu',
									 'continut' => 'continut',
									 'img' => 'img',
									 'id_obj' => 'id_obj',
									 'data' => 'data',
									 'unde' => 'unde',
									 'thumb' => 'thumb'
									 ),
				'meciuri' => array('id_meci' => 'id_meci',
								   'areRezultat' => 'are_rezultat',
								   'dataStart' => 'data_start',
								   'dataSfarsit' => 'data_sfarsit',
								   'echipaGazda' => 'echipa_gazda',
								   'echipaOaspete' => 'echipa_oaspete',
								   'cota1' => 'cota1',
								   'cotaX' => 'cotax',
								   'cota2' => 'cota2',
								   'data' => 'data',
								   'dataMeci' => 'data_meci',
								   'Rezultat1' => 'rezultat1',
								   'Rezultat2' => 'rezultat2'
								  ),
				'meciStats' => array('id_meci' => 'id_meci'),
				'pariuri' => array('id' => 'id',
								   'id_meci' => 'id_meci',
								   'id_bilet' => 'id_bilet',
								   'id_user' => 'id_user',
								   'cota' => 'cota',
								   'data' => 'data',
								   'castigator' => 'castigator',
								   'verificat' => 'verificat',
								   'castig' => 'castig'
								  ),
				'forumForums' => array('id' => 'id',
									  'titlu' => 'titlu',
									  'descriere' => 'comentariu',
									  'id_categ' => 'id_categ',
									  'lastPost' => 'ID_lastPost',
									  'nrPosts' => 'nrPosts',
									  'nrTopics' => 'nrTopics'
									 ),
				'forumCateg' => array('id' => 'id',
									  'order' => 'order',
									  'titlu' => 'titlu'
									 ),
				'forumTopics' => array('id' => 'id',
									   'id_forums' => 'id_forums',
									   'titlu' => 'titlu',
									   'starter' => 'starter',
									   'replici' => 'replici',
									   'vizualizari' => 'vizualizari',
									   'lastPost' => 'ID_lastPost',
									   'data' => 'data',
									   'stare' => 'stare',
									   'vizionari' => 'vizualizari'
									  ),
				'forumPosts' => array('id' => 'id',
									  'id_topic' => 'id_topic',
									  'autor' => 'autor',
									  'body' => 'body',
									  'data' => 'data',
									  'titlu' => 'titlu'
									  ),
				'comentarii' => array('id' => 'id',
									  'id_obj' => 'id_obj',
									  'unde' => 'obj',
									  'data' => 'data',
									  'bad' => 'bad',
									  'good' => 'good',
									  'user' => 'user',
									  'body' => 'body'
									 ),
				'noteComentarii' => array('id_comm' => 'id_comentariu',
										  'id_user' => 'id_user'),
				'pms' => array('id' => 'id',
							'from' => 'from',
							'data' => 'data',
							'to' => 'to',
							'mesaj' => 'mesaj',
							'stare' => 'stare',
							'citit' => 'citit',
							'titlu' => 'titlu'
							),
				'ignore' => array('id' => 'id',
								  'from' => 'from',
								  'to' => 'to'	
									),
				'fotoGalery' => array('id' => 'id',
									  'titlu' => 'titlu',
									  'data' => 'data',
									  'votanti' => 'votanti',
									  'media' => 'media'
									 ),
				'fotos' => array('id' => 'id',
								 'idGalery' => 'id_galery',
								 'src' => 'img',
								 'src_thumb' => 'thumb',
								 'urcataDe' => 'urcat_de'
								),
				'newsletters' => array('id' => 'id',
									   'titlu' => 'titlu',
									   'body' => 'body',
									   'prop' => 'proposer',
									   'data' => 'data',
									   'dataAdaugare' => 'data_adaugare',
									   'trimis' => 'trimis'
										),
				'bugete' => array('id' => 'id',
								  'idUser' => 'id_user',
								  'cont' => 'cont',
								  'data' => 'data'
								  ),
				'views' => array('id' => 'id',
								 'unde' => 'obj',
								 'care' => 'id_obj',
								 'ip' => 'ip'
								)
				
				);
				
$TABLES = array('users' => 'ftbf_users',
				'banned' => 'ftbf_banned',
				'stiri' => 'ftbf_stiri',
				'facils' => 'ftbf_facilitati',
				'tags' => 'ftbf_tags',
				'userTags' => 'ftfb_userTags',
				'allTags' => 'ftbf_allTags',
				'newsUsers' => 'ftbf_newsUsers',
				'propuneri' => 'ftbf_propuneri',
				'meciuri' => 'ftbf_meciuri',
				'meciStats' => 'ftbf_meciStats',
				'pariuri' => 'ftbf_pariuri',
				'forumTopics' => 'ftbf_forum_topics',
				'forumCateg' => 'ftbf_forum_categs',
				'forumForums' => 'ftbf_forum_forums',
				'forumPosts' => 'ftbf_forum_posts',
				'comentarii' => 'ftbf_comentarii',
				'noteComentarii' => 'ftbf_note_comm',
				'pms' => 'ftbf_pms',
				'ignore' => 'ftbf_ignore',
				'fotoGalery' => 'ftbf_foto_galery',
				'fotos' => 'ftbf_fotos',
				'newsletters' => 'ftbf_newsletters',
				'bugete' => 'ftbf_bugete',
				'views' => 'ftbf_vizionari'
				);
				
?>