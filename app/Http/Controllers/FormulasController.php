<?php

namespace App\Http\Controllers;

use App\Repositories\FormulaRepository as Formula;
use App\Repositories\FormulaDetailRepository as FormulaDetail;
use App\Repositories\MaterialRepository as Material;
use App\Repositories\Criteria\Material\MaterialWhereNameLike;
use App\Repositories\Criteria\Formula\FormulasWithFormulaDetails;
use App\Models\Seed;
use DB;
use App\Models\Product;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Flash;

class FormulasController extends Controller
{
    private $formula;

    private $formulaDetail;

    private $material;

    public function __construct(Formula $formula, FormulaDetail $formulaDetail, Material $material)
    {
        $this->formula       = $formula;
        $this->formulaDetail = $formulaDetail;
        $this->material      = $material;
    }

    static function routes() {
        \Route::group(['prefix' => 'formulas'], function () {
            \Route::get(  '/',                     'FormulasController@index')              ->name('admin.formulas.index');
            \Route::post( '/',                     'FormulasController@store')              ->name('admin.formulas.store');
            \Route::get(  'create',                'FormulasController@create')             ->name('admin.formulas.create');
            \Route::get(  'search',                'FormulasController@materialSearch')     ->name('admin.formulas.search');
            \Route::get(  'productsearch',        'FormulasController@productSearch')      ->name('admin.formulas.product-search');
            \Route::get(  '{fId}',                 'FormulasController@show')               ->name('admin.formulas.show');
            \Route::patch('{fId}',                 'FormulasController@update')             ->name('admin.formulas.update');
            \Route::get(  '{fId}/edit',            'FormulasController@edit')               ->name('admin.formulas.edit');
            \Route::get(  '{fId}/delete',          'FormulasController@destroy')            ->name('admin.formulas.delete');
            \Route::post( 'add-purchase-order',    'FormulasController@addPurchaseOrder')   ->name('admin.formulas.add-porder');
            \Route::get(  '{fId}/get-materials',   'FormulasController@getMaterials')       ->name('admin.formulas.get-materials');
            \Route::get(  '{fId}/confirm-delete',  'FormulasController@getModalDelete')     ->name('admin.formulas.confirm-delete');
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $formulas = $this->formula->all();

        $page_title       = trans('admin/formulas/general.page.index.title');
        $page_description = trans('admin/formulas/general.page.index.description');

        return view('admin.formulas.index', compact('page_title', 'page_description', 'formulas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title       = trans('admin/formulas/general.page.create.title');
        $page_description = trans('admin/formulas/general.page.create.description');
                
        return view('admin.formulas.create', compact('page_title', 'page_description'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $formula = $this->formula->create(['product_id' => $request->input('product_id')]);

        $data = $request->input('material');
        foreach ($data as $key => $m) {
            $m['formula_id'] = $formula->id;
            $this->formulaDetail->create($m);
        }

        Flash::success( trans('admin/formulas/general.status.created') );

        return redirect('/admin/formulas');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $formula          = $this->formula->pushCriteria(new FormulasWithFormulaDetails())->find($id);
        
        $page_title       = trans('admin/formulas/general.page.show.title');
        $page_description = trans('admin/formulas/general.page.show.description', ['name' => $formula->product->name]);

        return view('admin.formulas.show', compact('page_title', 'page_description', 'formula'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $formula          = $this->formula->pushCriteria(new FormulasWithFormulaDetails())->find($id);
        
        $page_title       = trans('admin/formulas/general.page.edit.title');
        $page_description = trans('admin/formulas/general.page.edit.description', ['name' => $formula->product->name]);

        return view('admin.formulas.edit', compact('page_title', 'page_description'))->with('formula', $formula);
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
        $formula = $this->formula->find($id);
        $data    = $request->input('material');

        foreach ($formula->formulaDetails as $key => $detail) {
            $this->formulaDetail->delete($detail->id);
        }

        foreach ($data as $key => $m) {
            $m['formula_id'] = $id;
            $this->formulaDetail->create($m);
        }

        $this->formula->update(['updated_at' => date("Y-m-d H:i:s")], $id);

        Flash::success( trans('admin/formulas/general.status.updated') );

        return redirect()->route('admin.formulas.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->formula->delete($id);

        Flash::success( trans('admin/formulas/general.status.deleted') );

        return redirect('/admin/formulas');
    }

    public function materialSearch(Request $request)
    {
        $return_arr = null;
        
        $query      = $request->input('term');

        if ($query == '') {
            $query = $request->input('query');
        }

        $materials = $this->material->pushCriteria(new MaterialWhereNameLike($query))->all();

        foreach ($materials as $e) {
            $id    = $e->id;
            $name  = $e->name;
            $price = $e->price;

            $entry_arr = ['id' => $id, 'text' => $name, 'value' => $name, 'price' => $price];
            $return_arr[] = $entry_arr;
        }

        return $return_arr;
    }

    public function productSearch(Request $request) {
        $return_arr = null;

        $query      = $request->input('term');

        $products   = Product::where('name', 'like', '%'.$query.'%')->get();

        foreach ($products as $product) {
            if (!$product->formula) {
                $entry_arr = [
                    'id'              => $product->id,
                    'value'           => $product->name,
                ];
                $return_arr[] = $entry_arr;
            }
        }
        return response()->json($return_arr);
    }

     /**
     * Delete Confirm
     *
     * @param   int   $id
     * @return  View
     */
    public function getModalDelete($id)
    {
        $error   = null;
        
        $formula = $this->formula->find($id);

        $modal_title = trans('admin/formulas/dialog.delete-confirm.title');
        $modal_route = route('admin.formulas.delete', array('id' => $formula->id));
        $modal_body  = trans('admin/formulas/dialog.delete-confirm.body', ['id' => $formula->id, 'name' => $formula->product->name]);

        return view('modal_confirmation', compact('error', 'modal_route', 'modal_title', 'modal_body'));

    }

    public function getMaterials($id)
    {
        $formula   = $this->formula->pushCriteria(new FormulasWithFormulaDetails())->find($id);
        $materials = [];

        foreach ($formula->formulaDetails as $key => $material) {
            $materials[$key]['id']   = $material->material_id;
            $materials[$key]['name'] = $material->material->name;
            $materials[$key]['qty']  = $material->quantity;
        }

        return $materials;
    }

    public function addPurchaseOrder(Request $request) {
        $data = $request->input('chkMaterial');

        return view('admin.purchase-orders.create-from-formulas', compact('data'));
    }
}
