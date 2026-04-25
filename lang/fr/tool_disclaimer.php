<?php

$string['acknowledgement'] = 'Reconnaissance (tous les utilisateurs authentifiés)';
$string['acknowledgement_context_help'] = 'Une clause de reconnaissance est affichée à chaque utilisateur authentifié sur chaque page jusqu\'à ce qu\'il clique sur OK. Elle n\'est pas limitée à un cours et ne nécessite aucune sélection de rôle ou de cours.';
$string['cancel'] = 'Annuler';
$string['change_response'] = 'Modifier la réponse';
$string['context'] = 'Contexte';
$string['contextpath'] = 'Chemin du contexte';
$string['could_not_delete_disclaimer'] = 'Erreur : impossible de supprimer la clause';
$string['course'] = 'Cours';
$string['delete'] = 'Supprimer';
$string['delete_disclaimer_help'] = 'Êtes-vous sûr de vouloir supprimer cette clause ? Cela supprimera la clause, les rôles et les réponses des utilisateurs. Cette action ne peut pas être annulée.';
$string['disclaimer_exists'] = 'Une clause publiée existe déjà pour ce contexte. Vous ne pouvez avoir qu\'une seule clause publiée par contexte.';
$string['disclaimers'] = 'Clauses';
$string['early_alert'] = 'Alerte précoce';
$string['edit'] = 'Modifier';
$string['edit_disclaimer'] = 'Modifier la clause';
$string['field_required'] = 'Ce champ est obligatoire';
$string['filter'] = 'Filtrer';
$string['front_page_only'] = 'Uniquement sur la première page ?';
$string['front_page_only_help'] = 'Cette clause doit-elle être affichée uniquement sur la première page (/mon ou accueil) ?';
$string['message'] = 'Message';
$string['name'] = 'Nom';
$string['new'] = 'Nouveau';
$string['no'] = 'Non';
$string['not_your_disclaimer'] = 'Vous tentez d\'accéder à une clause qui ne vous appartient pas.';
$string['ok'] = 'J\'accepte';
$string['options'] = 'Options';
$string['original_message'] = 'Message original';
$string['pluginname'] = 'Clause';
$string['published'] = 'Publié';
$string['publish_from'] = 'Publier à partir de';
$string['publish_until'] = 'Publier jusqu\'à';
$string['reset'] = 'Réinitialiser';
$string['redirect_to_url'] = 'Rediriger vers l\'URL';
$string['redirect_to_url_help'] = 'Si annulé ou refusé, l\'utilisateur sera redirigé vers cette URL. Laissez vide pour rester sur la page.';
$string['subject'] = 'Sujet';
$string['system'] = 'Système';
$string['update_published_status'] = 'Mettre à jour le statut de publication de la clause';
$string['use_published_date'] = 'Utiliser une plage de dates pour la publication ?';

// Chaînes de retrait en libre-service du menu utilisateur.
$string['withdraw_disclaimer'] = 'Retirer la clause';
$string['withdraw_title'] = 'Retirer des clauses';
$string['withdraw_intro'] = 'Sélectionnez les clauses acceptées dont vous souhaitez vous retirer.';
$string['withdraw_select_all_disclaimers'] = 'Toutes les clauses';
$string['withdraw_now'] = 'Retirer';
$string['withdraw_confirm_title'] = 'Confirmer le retrait';
$string['withdraw_confirm_message'] = 'Êtes-vous sûr de vouloir vous retirer des clauses sélectionnées ? Vous verrez à nouveau ces clauses lors de votre prochaine visite.';
$string['withdraw_none'] = 'Vous n\'avez accepté aucune clause.';
$string['withdraw_select_one'] = 'Sélectionnez au moins une clause ou choisissez « Toutes les clauses ».';
$string['withdraw_success'] = 'Retrait réussi. {$a} enregistrement(s) de réponse de clause supprimé(s).';
$string['withdraw_invalid_userid'] = 'Entrez un ID utilisateur valide supérieur à zéro.';

// Page de retrait administrateur (Administration du site -> Cours -> Clauses).
$string['admin_withdraw_menu_label'] = 'Retirer la clause de l\'utilisateur';
$string['admin_withdraw_title'] = 'Retirer les réponses de clause au nom d\'un utilisateur';
$string['admin_withdraw_search_heading'] = 'Trouver un utilisateur';
$string['admin_withdraw_search_help'] = 'Recherchez et sélectionnez un utilisateur par son nom ou son adresse e-mail, puis chargez ses clauses acceptées.';
$string['admin_withdraw_userid'] = 'Utilisateur';
$string['admin_withdraw_load_user'] = 'Charger l\'utilisateur';
$string['admin_withdraw_disclaimers_heading'] = 'Clauses acceptées';
$string['admin_withdraw_none'] = 'Cet utilisateur n\'a accepté aucune clause.';
$string['admin_withdraw_confirm_message'] = 'Êtes-vous sûr de vouloir retirer les clauses sélectionnées pour {$a} ? L\'utilisateur verra à nouveau ces clauses lors de sa prochaine visite.';
$string['yes'] = 'Oui';

// Page des réponses utilisateur
$string['accepted'] = 'Accepté';
$string['actions'] = 'Actions';
$string['all'] = 'Tous';
$string['attempt'] = 'Tentative';
$string['declined'] = 'Refusé';
$string['disclaimer_name'] = 'Nom de la clause';
$string['response_reset_success'] = 'La réponse de l\'utilisateur a été réinitialisée avec succès';
$string['response_status'] = 'Statut de réponse';
$string['reset_response'] = 'Réinitialiser la réponse';
$string['reset_response_confirm'] = 'Êtes-vous sûr de vouloir réinitialiser la réponse de l\'utilisateur {$a->username} ({$a->email}) pour la clause « {$a->disclaimer} » dans le contexte « {$a->context} » ? Cela permettra à l\'utilisateur de voir à nouveau la clause.';
$string['timecreated'] = 'Date de réponse';
$string['userid'] = 'ID utilisateur';
$string['userid_help'] = 'Entrez l\'ID utilisateur numérique pour rechercher un utilisateur spécifique.';
$string['user_responses'] = 'Réponses des utilisateurs';

// Droits d\'accès
$string['disclaimer:delete'] = 'Supprimer l\'enregistrement de clause';
$string['disclaimer:edit'] = 'Modifier l\'enregistrement de clause';
$string['disclaimer:reports'] = 'Afficher les rapports de clause';
$string['disclaimer:view'] = 'Afficher les enregistrements de clause';
$string['disclaimer:withdrawmanage'] = 'Retirer les réponses de clause au nom d\'autres utilisateurs';

// Rôles
$string['role_authenticated_user'] = 'Utilisateur authentifié';
$string['role_authenticated_user_home'] = 'Utilisateur authentifié sur la page d\'accueil du site';
$string['role_course_creator'] = 'Créateur de cours';
$string['role_guest'] = 'Invité';
$string['role_manager'] = 'Gestionnaire';
$string['role_non-editing_teacher'] = 'Enseignant non éditeur';
$string['role_student'] = 'Étudiant';
$string['role_teacher'] = 'Enseignant';

/**
 * Confidentialité
 */
$string['privacy:metadata:tool_disclaimer'] = 'L\'outil Clause stocke des informations sur les clauses, y compris l\'utilisateur qui a modifié en dernier chaque clause.';
$string['privacy:metadata:tool_disclaimer:usermodified'] = 'L\'ID de l\'utilisateur qui a modifié en dernier la clause.';
$string['privacy:metadata:tool_disclaimer_role'] = 'L\'outil Clause stocke les attributions de rôles pour les clauses, y compris l\'utilisateur qui a modifié en dernier chaque attribution.';
$string['privacy:metadata:tool_disclaimer_role:usermodified'] = 'L\'ID de l\'utilisateur qui a modifié en dernier l\'attribution de rôle.';
$string['privacy:metadata:tool_disclaimer_log'] = 'L\'outil Clause stocke les réponses des utilisateurs aux clauses, y compris l\'utilisateur qui a réagi et l\'utilisateur qui a modifié en dernier l\'entrée du journal.';
$string['privacy:metadata:tool_disclaimer_log:userid'] = 'L\'ID de l\'utilisateur qui a répondu à la clause.';
$string['privacy:metadata:tool_disclaimer_log:usermodified'] = 'L\'ID de l\'utilisateur qui a modifié en dernier l\'entrée du journal.';

// Chemins d\'export de confidentialité
$string['privacy:disclaimers'] = 'Clauses';
$string['privacy:roles'] = 'Rôles';
$string['privacy:userresponses'] = 'Réponses des utilisateurs';

