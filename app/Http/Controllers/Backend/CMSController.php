<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CMS;
use App\FAQQuestion;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;

class CMSController extends Controller {
	public $cms;
	public $columns;

	public function __construct() {
		$this->cms = new CMS;
		$this->columns = [
			"sno", "name", "action",
		];

		$this->middleware('permission:cms-list|cms-edit', ['only' => ['index','update']]);
        $this->middleware('permission:cms-edit', ['only' => ['edit','update']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		return view('backend.cms.index');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function cmsAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->cms->fetchCMS($request, $this->columns);
		$total = $records->get();
		if (isset($request->start)) {
			$cms = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$cms = $records->offset($request->start)->limit(count($total))->get();
		}
		// echo $total;
		$result = [];
		$i = 1;
		foreach ($cms as $list) {
			$data = [];
			$data['sno'] = $i++;
			$data['name'] = $list->name;
			$action = '';

			if (Helper::checkAccess(route('editCMS'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editCMS', ['id' => $list->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			if (Helper::checkAccess(route('viewCMS'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewCMS', ['id' => $list->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			}
			$data['action'] = $action;

			$result[] = $data;
		}
		$data = json_encode([
			'data' => $result,
			'recordsTotal' => count($total),
			'recordsFiltered' => count($total),
		]);
		echo $data;

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id = null) {
		if (isset($id) && $id != null) {
			$cms = CMS::where('id', $id)->first();
			if (isset($cms->id)) {
				$faqs = [];
				if ($cms->slug == 'faq') {

					$faqs = FAQQuestion::fetchquestions($cms->slug);
				}
				return view('backend.cms.view', compact('cms', 'faqs'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('cms');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('cms');
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id = null) {
		if (isset($id) && $id != null) {
			$cms = CMS::where('id', $id)->first();
			if (isset($cms->id)) {
				$faqs = [];
				if ($cms->slug == 'faq') {
					$faqs = FAQQuestion::fetchquestions($cms->slug);
				}
				$type = 'Edit';
				return view('backend.cms.create', compact('cms', 'faqs', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('cms');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('cms');
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id = null) {
		if (isset($id) && $id != null) {
			$cms = CMS::where('id', $id)->first();
			if (isset($cms->id)) {
				try {
					if ($cms->slug == 'faq') {
						$count = 0;
						foreach ($request->all() as $key => $value) {
							if (stripos($key, 'faqid') !== false) {
								$count++;
							}
						}
						FAQQuestion::saveQuestions($count, $request->all());
					}else{
						$cms->content = $request->cms_content;

						if ($cms->original_content == '') {
							$cms->original_content = $request->cms_content;
						}

						$cms->save();
					}
					$request->session()->flash('success', 'Page updated successfully');
					return redirect()->route('cms');

				} catch (Exception $e) {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('cms');
				}

			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('cms');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('cms');
		}

	}
	/**
	 * Remove the specified FQA from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request) {
		if (isset($request->id) && $request->id != null) {
			$faq = FAQQuestion::find($request->id);

			if (isset($faq->id)) {
				$faq->status = 'delete';
				if ($faq->save()) {
					echo json_encode(["status" => 1, 'message' => 'FAQ deleted successfully.']);
				} else {
					echo json_encode(["status" => 0, 'message' => 'Some error occurred while deleting the FAQ']);
				}
			} else {
				echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
			}

		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}

	public function privacypolicy() {
		$cms = CMS::where('slug', 'privacy-policy')->first();
		return view('backend.cms.page', compact('cms'));
	}
}
