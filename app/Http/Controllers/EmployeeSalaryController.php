<?php

namespace App\Http\Controllers;

use App\Employee;
use App\EmployeeSalary;
use Illuminate\Http\Request;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use App\State;
//use App\Country;
class EmployeeSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('auth')->only(["index", "create", "store", "edit", "update", "search", "destroy"]);
    }

    public function index()
    {
        $salary = DB::table('employee_salary')
            ->leftJoin('salary', 'employee_salary.employee_id', '=', 'employees.id')
            ->select('employee_salary.id', 'employee_salary.salary', 'employees.firstname as employees_name', 'employees.id as employee_id')
            ->paginate(5);
        return view('system-mgmt/salary/index', ['salary' => $salary]);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Employee::findOrFail($request['employee_id']);
        $this->validateInput($request);
        EmployeeSalary::create([
            'salary' => $request['salary'],
            'employee_id' => $request['employee_id']
        ]);

        return redirect()->intended('system-management/salary');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $salary = EmployeeSalary::find($id);
        // Redirect to state list if updating state wasn't existed
        if ($salary == null || count($salary) == 0) {
            return redirect()->intended('/system-management/salary');
        }

        $firstname = Employee::all();
        return view('system-mgmt/salary/edit', ['salary' => $salary, 'firstname' => $firstname]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $salary = EmployeeSalary::findOrFail($id);
        $this->validate($request, [
            'salary' => 'required|max:60'
        ]);
        $input = [
            'salary' => $request['salary'],
            'employee_id' => $request['employee_id']
        ];
        EmployeeSalary::where('id', $id)
            ->update($input);

        return redirect()->intended('system-management/salary');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        EmployeeSalary::where('id', $id)->delete();
        return redirect()->intended('system-management/salary');
    }
    public function loadSalary($salaryId) {
        $salary = EmployeeSalary::where('employee_id', '=', $salaryId)->get(['id', 'salary']);

        return response()->json($salary);
    }
    public function search(Request $request) {
        $constraints = [
            'salary' => $request['salary']
        ];

        $salary = $this->doSearchingQuery($constraints);
        return view('system-mgmt/salary/index', ['salary' => $salary, 'searchingVals' => $constraints]);
    }

    private function doSearchingQuery($constraints) {
        $query = EmployeeSalary::query();
        $fields = array_keys($constraints);
        $index = 0;
        foreach ($constraints as $constraint) {
            if ($constraint != null) {
                $query = $query->where( $fields[$index], 'like', '%'.$constraint.'%');
            }

            $index++;
        }
        return $query->paginate(5);
    }
    private function validateInput($request) {
        $this->validate($request, [
            'salary' => 'required|max:60|unique:salary'
        ]);
    }
}
