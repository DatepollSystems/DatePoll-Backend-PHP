<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use Illuminate\Http\Request;

class SubgroupController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function getAll()
  {
    $subgroups = Subgroup::all();

    return response(['msg' => 'List of all subgroups', 'subgroups' => $subgroups], 200);
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
      'description' => 'max:65535',
      'group_id' => 'required|integer'
    ]);

    $group_id = $request->input('group_id');

    if(Group::find($group_id) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');

    $subgroup = new Subgroup([
      'name' => $name,
      'description' => $description,
      'group_id' => $group_id
    ]);

    if($subgroup->save()) {
      $subgroup->view_subgroup = [
        'href' => 'api/v1/management/subgroups/'.$subgroup->id,
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Subgroup created',
        'subgroup' => $subgroup
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
    $subgroup = Subgroup::find($id);
    if($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $subgroup->view_subgroups = [
      'href' => 'api/v1/management/subgroups',
      'method' => 'GET'
    ];

    $response = [
      'msg' => 'Subgroup information',
      'subgroup' => $subgroup
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
      'description' => 'max:65535',
      'group_id' => 'required|integer'
    ]);

    $subgroup = Subgroup::find($id);

    if($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $group_id = $request->input('group_id');

    if(Group::find($group_id) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');

    $subgroup->name = $name;
    $subgroup->description = $description;
    $subgroup->group_id = $group_id;

    if($subgroup->save()) {
      $subgroup->view_subgroup = [
        'href' => 'api/v1/management/subgroup/'.$subgroup->id,
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Subgroup updated',
        'subgroup' => $subgroup
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
    $subgroup = Subgroup::find($id);
    if($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    if(!$subgroup->delete()) {
      return response()->json(['msg' => 'Subgroup deletion failed'], 500);
    }

    $response = [
      'msg' => 'Subgroup deleted',
      'create' => [
        'href' => 'api/v1/management/subgroup',
        'method' => 'POST',
        'params' => 'name, description, group_id'
      ]
    ];

    return response()->json($response, 200);
  }
}
