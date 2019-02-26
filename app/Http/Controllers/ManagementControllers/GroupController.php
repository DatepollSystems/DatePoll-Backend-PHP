<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\Groups\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function getAll()
  {
    $groups = Group::all();
    foreach($groups as $group) {
      $group->subgroups = $group->subgroups();
    }

    return response(['msg' => 'List of all groups', 'groups' => $groups], 200);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'description' => 'max:65535'
    ]);

    $name = $request->input('name');
    $description = $request->input('description');

    $group = new Group([
      'name' => $name,
      'description' => $description
    ]);

    if($group->save()) {
      $group->view_group = [
        'href' => 'api/v1/management/groups/'.$group->id,
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Group created',
        'group' => $group
      ];

      return response()->json($response, 201);
    }

    $response = [
      'msg' => 'An error occurred'
    ];

    return response()->json($response, 500);
  }

  /**
   * Display the specified resource.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function getSingle($id)
  {
    $group = Group::find($id);
    if($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $group->subgroups = $group->subgroups();

    $group->view_groups = [
      'href' => 'api/v1/management/groups',
      'method' => 'GET'
    ];

    $response = [
      'msg' => 'Group information',
      'group' => $group
    ];
    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  int $id
   * @return \Illuminate\Http\Response
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request, $id)
  {
    $this->validate($request, [
      'name' => 'required|max:255|min:1',
      'description' => 'max:65535'
    ]);

    $group = Group::find($id);

    if($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }


    $name = $request->input('name');
    $description = $request->input('description');

    $group->name = $name;
    $group->description = $description;

    if($group->save()) {
      $group->view_group = [
        'href' => 'api/v1/management/group/'.$group->id,
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Group updated',
        'group' => $group
      ];

      return response()->json($response, 201);
    }

    $response = [
      'msg' => 'An error occurred'
    ];

    return response()->json($response, 500);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function delete($id)
  {
    $group = Group::find($id);
    if($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    if(!$group->delete()) {
      return response()->json(['msg' => 'Group deletion failed'], 500);
    }

    $response = [
      'msg' => 'Group deleted',
      'create' => [
        'href' => 'api/v1/management/group',
        'method' => 'POST',
        'params' => 'name, description'
      ]
    ];

    return response()->json($response, 200);
  }
}
