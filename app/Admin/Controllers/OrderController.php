<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use App\Admin\Extensions\Tools\HandleOrders;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->add('处理成功', new HandleOrders(3));
                $batch->add('处理失败', new HandleOrders(4));
            });
        });
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            //$actions->disableEdit();
            $actions->disableView();
        });
        $grid->model()->orderBy('id', 'desc');
        $grid->id('订单id');
        $grid->trade_no('订单号');
        $grid->name('订单名称');
        $grid->goods_name('商品名称');
        $grid->unit_price('商品单价');
        $grid->count('购买数量');
        $grid->total_price('订单总价');
        $grid->pay_account('充值账号');
        $grid->email('邮件');
        $grid->type('订单类型')->display(function ($type) {
            switch ($this->type) {
                case 1:
                    return '手动发卡';
                case 2:
                    return '自动发卡';
            }
        });
        $grid->out_trade_no('第三方支付号');
        $grid->pay_type('支付方式')->display(function ($payType) {
            switch ($payType) {
                case 1:
                    return '微信支付';
                case 2:
                    return '支付宝支付';
                default:
                    return '';
            }
        });
        $grid->password('查询密码');
        $grid->status('订单状态')->display(function ($status) {
            switch ($status) {
                case 0:
                    return '未支付';
                case 1:
                    return '已支付';
                case 2:
                    return '过期';
                case 3:
                    return '处理成功';
                case 4:
                    return '处理失败';
            }
        });
        $grid->created_at('创建时间');
        $grid->pay_time('支付时间');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->id('ID');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->display('ID');
        $form->display('Created at');
        $form->display('Updated at');

        return $form;
    }

    public function status(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;
        Order::whereIn('id', $ids)->update(['status' => $action]);
        return ['code' => 1];
    }

}