<?php

namespace App\Controllers;

use App\Models\ItemsModel;

class ItemController extends BaseController
{
    public function index()
    {
        $itemsModel = new ItemsModel();
        $items = $itemsModel->findAll();

        return view('items/index', ['items' => $items]);
    }

    public function create()
    {
        return view('items/create');
    }

    public function store()
    {
        $rules = [
            'code'        => 'required|min_length[2]|max_length[50]',
            'name'        => 'required|min_length[3]|max_length[255]',
            'category'    => 'required|max_length[100]',
            'buy_price'   => 'required|numeric|greater_than_equal_to[0]',
            'sell_price'  => 'required|numeric|greater_than_equal_to[0]',
            'stock'       => 'required|integer',
            'min_stock'   => 'required|integer|greater_than_equal_to[0]',
            'description' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $itemsModel = new ItemsModel();
        $code = $this->request->getPost('code');

        // Check unique code within the current clinic
        $existing = $itemsModel->where('code', $code)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', "Item code '{$code}' already exists in your clinic.");
        }

        $itemData = [
            'code'        => strtoupper($code),
            'name'        => $this->request->getPost('name'),
            'category'    => $this->request->getPost('category'),
            'buy_price'   => $this->request->getPost('buy_price'),
            'sell_price'  => $this->request->getPost('sell_price'),
            'stock'       => $this->request->getPost('stock'),
            'min_stock'   => $this->request->getPost('min_stock'),
            'description' => $this->request->getPost('description') ?: null,
            'status'      => 1, // Active by default
        ];

        $itemsModel->insert($itemData);

        return redirect()->to('/items')->with('success', 'Inventory item added successfully.');
    }

    public function edit($id)
    {
        $itemsModel = new ItemsModel();
        $item = $itemsModel->find($id);

        if (!$item) {
            return redirect()->to('/items')->with('error', 'Inventory item not found.');
        }

        return view('items/edit', ['item' => $item]);
    }

    public function update($id)
    {
        $itemsModel = new ItemsModel();
        $item = $itemsModel->find($id);

        if (!$item) {
            return redirect()->to('/items')->with('error', 'Inventory item not found.');
        }

        $rules = [
            'code'        => 'required|min_length[2]|max_length[50]',
            'name'        => 'required|min_length[3]|max_length[255]',
            'category'    => 'required|max_length[100]',
            'buy_price'   => 'required|numeric|greater_than_equal_to[0]',
            'sell_price'  => 'required|numeric|greater_than_equal_to[0]',
            'stock'       => 'required|integer',
            'min_stock'   => 'required|integer|greater_than_equal_to[0]',
            'description' => 'permit_empty|max_length[1000]',
            'status'      => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = $this->request->getPost('code');

        // Check unique code in the clinic excluding this item id
        $existing = $itemsModel->where('code', $code)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', "Item code '{$code}' is already taken by another item in your clinic.");
        }

        $itemData = [
            'code'        => strtoupper($code),
            'name'        => $this->request->getPost('name'),
            'category'    => $this->request->getPost('category'),
            'buy_price'   => $this->request->getPost('buy_price'),
            'sell_price'  => $this->request->getPost('sell_price'),
            'stock'       => $this->request->getPost('stock'),
            'min_stock'   => $this->request->getPost('min_stock'),
            'description' => $this->request->getPost('description') ?: null,
            'status'      => (int)$this->request->getPost('status'),
        ];

        $itemsModel->update($id, $itemData);

        return redirect()->to('/items')->with('success', 'Inventory item updated successfully.');
    }

    public function delete($id)
    {
        $itemsModel = new ItemsModel();
        $item = $itemsModel->find($id);

        if (!$item) {
            return redirect()->to('/items')->with('error', 'Inventory item not found.');
        }

        $itemsModel->delete($id);

        return redirect()->to('/items')->with('success', 'Inventory item deleted successfully.');
    }
}
