<?php
/**
 * This source file is part of the open source project
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2020, Packet Tide, LLC (https://www.packettide.com)
 * @license   https://expressionengine.com/license Licensed under Apache License, Version 2.0
 */

namespace ExpressionEngine\Controller\Members\Profile;

use CP_Controller;

/**
 * Member Roles Settings Controller
 */
class Roles extends Profile {

	private $base_url = 'members/profile/roles';

	public function __construct()
	{
		parent::__construct();

		if ($this->member->member_id == ee()->session->userdata('member_id')
		    && $this->member->isSuperAdmin())
		{
			show_error(lang('cannot_change_your_group'));
		}
	}

	/**
	 * Role assignment
	 */
	public function index()
	{
		$this->base_url = ee('CP/URL')->make($this->base_url, $this->query_string);
		$roles = ee('Model')->get('Role')->order('name', 'asc');

		if ( ! ee('Permission')->isSuperAdmin())
		{
			$roles = $roles->filter('is_locked', FALSE);
		}

		$roles = $roles->all()
			->getDictionary('role_id', 'name');

		if (!ee('Permission')->isSuperAdmin() && !array_key_exists($this->member->role_id, $roles))
		{
			show_error(lang('unauthorized_access'), 403);
		}

		$additional_roles_section = [];
		if (ee('Permission')->isSuperAdmin()) {
			$additional_roles_section[] = [
				'title' => 'role_groups',
				'desc' => 'role_groups_desc',
				'fields' => [
					'role_groups' => [
						'type' => 'checkbox',
						'choices' => ee('Model')->get('RoleGroup')->order('name', 'asc')->all()->getDictionary('group_id', 'name'),
						'value' => $this->member->RoleGroups->pluck('group_id'),
						'no_results' => [
							'text' => sprintf(lang('no_found'), lang('role_groups'))
						]
					]
				]
			];
		}
		$additional_roles_section[] = [
			'title' => 'roles',
			'desc' => 'roles_desc',
			'fields' => [
				'roles' => [
					'type' => 'checkbox',
					'choices' => $roles,
					'value' => $this->member->Roles->pluck('role_id'),
					'no_results' => [
						'text' => sprintf(lang('no_found'), lang('roles'))
					]
				]
			]
		];

		$vars['sections'] = [
			[
				ee('CP/Alert')->makeInline('permissions-warn')
					->asWarning()
					->addToBody(lang('access_privilege_warning'))
					->addToBody(
						sprintf(lang('access_privilege_caution'), '<span class="icon--caution" title="exercise caution"></span>'),
						'caution'
					)
					->cannotClose()
					->render(),
				[
					'title' => 'primary_role',
					'desc' => 'primary_role_desc',
					'caution' => TRUE,
					'fields' => [
						'role_id' => [
							'type' => 'radio',
							'required' => TRUE,
							'choices' => $roles,
							'value' => $this->member->role_id,
							'no_results' => [
								'text' => sprintf(lang('no_found'), lang('roles'))
							]
						]
					]
				]
			],
			'additional_roles' => $additional_roles_section
		];

		$rules = [
			[
				'field' => 'role_id',
				'label' => 'lang:role',
				'rules' => 'callback__valid_role'
			],
		];

		if ( ! ee('Session')->isWithinAuthTimeout())
		{
			$vars['sections']['secure_form_ctrls'] = array(
				array(
					'title' => 'existing_password',
					'desc' => 'existing_password_exp',
					'fields' => array(
						'password_confirm' => array(
							'type'      => 'password',
							'required'  => TRUE,
							'maxlength' => PASSWORD_MAX_LENGTH
						)
					)
				)
			);

			$rules[] = [
				'field' => 'password_confirm',
				'label' => 'lang:password',
				'rules' => 'required|auth_password[useAuthTimeout]'
			];

		}

		ee()->form_validation->set_rules($rules);

		if (AJAX_REQUEST)
		{
			ee()->form_validation->run_ajax();
			exit;
		}
		elseif (ee()->form_validation->run() !== FALSE)
		{
			$this->member->role_id = ee('Request')->post('role_id');

			if (ee('Permission')->isSuperAdmin()) {
				$groups = ee('Request')->post('role_groups');
				$this->member->RoleGroups = ($groups) ? ee('Model')->get('RoleGroup', $groups)->all() : NULL;
			}

			$roles = ee('Request')->post('roles');
			$this->member->Roles = ($roles) ? ee('Model')->get('Role', $roles)->all() : NULL;

			$this->member->save();

			ee('CP/Alert')->makeInline('shared-form')
				->asSuccess()
				->withTitle(lang('member_updated'))
				->addToBody(lang('member_updated_desc'))
				->defer();
			ee()->functions->redirect($this->base_url);

		}
		elseif (ee()->form_validation->errors_exist())
		{
			ee('CP/Alert')->makeInline('shared-form')
				->asIssue()
				->withTitle(lang('settings_save_error'))
				->addToBody(lang('settings_save_error_desc'))
				->now();
		}

		ee()->view->base_url = $this->base_url;
		ee()->view->ajax_validate = TRUE;
		ee()->view->cp_page_title = lang('member_role_assignment');
		ee()->view->save_btn_text = 'btn_authenticate_and_save';
		ee()->view->save_btn_text_working = 'btn_saving';
		ee()->cp->render('settings/form', $vars);
	}

	public function _valid_role($role)
	{
		$roles = ee('Model')->get('Role', $role);

		if ( ! ee('Permission')->isSuperAdmin())
		{
			$roles = $roles->filter('is_locked', FALSE);
		}

		$num_roles = $roles->count();

		if ($num_roles == 0)
		{
			return FALSE;
		}

		return TRUE;
	}
}
// END CLASS

// EOF
