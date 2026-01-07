<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    // ===== Constants (CSV) =====
    private const CSV_REL_PATH = 'app/data/products-data.csv'; // storage/app/data/products-data.csv
    private const CSV_DELIMITER = ',';
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = ''; // tu utilisais '' => on garde le même comportement

    private const HEADERS = [
        'id',
        'name',
        'description',
        'category',
        'price',
        'currency',
        'stock',
        'color',
        'size',
        'availability',
    ];

    // ===== Constants (Views/Routes) =====
    private const VIEW_HOME = 'home';
    private const VIEW_LIST = 'Productlist';
    private const VIEW_CREATE = 'ProductCreate';
    private const VIEW_EDIT = 'ProductEdit';

    private const ROUTE_LIST = 'afficher-liste';

    // ===== CSV: Read =====
    public function readCSVData(): array
    {
        $fileContent = [];

        try {
            $filePath = storage_path(self::CSV_REL_PATH);

            if (!file_exists($filePath)) {
                return [];
            }

            $myFile = fopen($filePath, 'r');
            if ($myFile === false) {
                return [];
            }

            $i = 0;
            while (($row = fgetcsv($myFile, 1000, self::CSV_DELIMITER)) !== false) {
                // Skip header
                if ($i === 0) {
                    $i++;
                    continue;
                }

                // Pad row to avoid undefined offsets
                $row = array_pad($row, count(self::HEADERS), '');

                $fileContent[] = $this->normalizeProduct([
                    'id'           => $row[0],
                    'name'         => $row[1],
                    'description'  => $row[2],
                    'category'     => $row[3],
                    'price'        => $row[4],
                    'currency'     => $row[5],
                    'stock'        => $row[6],
                    'color'        => $row[7],
                    'size'         => $row[8],
                    'availability' => $row[9],
                ]);

                $i++;
            }

            fclose($myFile);
        } catch (\Throwable $e) {
            // Pour TP: tu peux logger au lieu de dd
            // logger()->error("CSV read error: ".$e->getMessage());
            return [];
        }

        return $fileContent;
    }

    // ===== CSV: Write =====
    public function writeCSVData(array $productData): void
    {
        $filePath = storage_path(self::CSV_REL_PATH);

        // On garde la même logique "file_exists" (comme ton code)
        if (!file_exists($filePath)) {
            return;
        }

        $myFile = fopen($filePath, 'w');
        if ($myFile === false) {
            return;
        }

        // Header
        fputcsv($myFile, self::HEADERS, self::CSV_DELIMITER, self::CSV_ENCLOSURE, self::CSV_ESCAPE);

        // Rows: on force l'ordre des colonnes
        foreach ($productData as $p) {
            $p = $this->normalizeProduct($p);

            $line = [];
            foreach (self::HEADERS as $key) {
                $line[] = $p[$key] ?? '';
            }

            fputcsv($myFile, $line, self::CSV_DELIMITER, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        }

        fclose($myFile);
    }

    // ===== Helpers =====
    private function normalizeProduct(array $p): array
    {
        return [
            'id'           => trim((string)($p['id'] ?? '')),
            'name'         => trim((string)($p['name'] ?? '')),
            'description'  => trim((string)($p['description'] ?? '')),
            'category'     => trim((string)($p['category'] ?? '')),
            'price'        => ($p['price'] ?? '') === '' ? 0 : (float)$p['price'],
            'currency'     => trim((string)($p['currency'] ?? '')),
            'stock'        => ($p['stock'] ?? '') === '' ? 0 : (int)$p['stock'],
            'color'        => trim((string)($p['color'] ?? '')),
            'size'         => trim((string)($p['size'] ?? '')),
            'availability' => trim((string)($p['availability'] ?? '')),
        ];
    }

    private function findIndexById(array $products, string $id): ?int
    {
        foreach ($products as $i => $p) {
            if ((string)($p['id'] ?? '') === (string)$id) {
                return $i;
            }
        }
        return null;
    }

    private function generateId(): string
    {
        // ID alphanum unique, simple pour TP
        return strtoupper(bin2hex(random_bytes(4))); // ex: 8 chars hex
    }

    // ===== Actions (defined/completed) =====

    // Home
    public function index()
    {
        return view(self::VIEW_HOME);
    }

    // List
    public function show()
    {
        $products = $this->readCSVData();
        return view(self::VIEW_LIST, ['products' => $products]);
    }

    // Create form
    public function create()
    {
        return view(self::VIEW_CREATE);
    }

    // Store new product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'category'     => ['nullable', 'string', 'max:255'],
            'price'        => ['required', 'numeric', 'min:0'],
            'currency'     => ['required', 'string', 'max:10'],
            'stock'        => ['required', 'integer', 'min:0'],
            'color'        => ['nullable', 'string', 'max:50'],
            'size'         => ['nullable', 'string', 'max:50'],
            'availability' => ['required', 'string', 'max:50'],
        ]);

        $products = $this->readCSVData();

        $newProduct = $this->normalizeProduct(array_merge($validated, [
            'id' => $this->generateId(),
        ]));

        $products[] = $newProduct;

        $this->writeCSVData($products);

        return redirect()
            ->route(self::ROUTE_LIST)
            ->with('success', 'Product created successfully.');
    }

    // Delete
    public function delete($id)
    {
        try {
            $products = $this->readCSVData();

            $index = $this->findIndexById($products, (string)$id);
            if ($index === null) {
                return redirect()
                    ->route(self::ROUTE_LIST)
                    ->with('error', 'Produit introuvable.');
            }

            unset($products[$index]);

            // Re-indexation pour éviter des trous
            $products = array_values($products);

            $this->writeCSVData($products);

            return redirect()
                ->route(self::ROUTE_LIST)
                ->with('success', 'Product deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->route(self::ROUTE_LIST)
                ->with('error', 'An error occurred while deleting the product: ' . $e->getMessage());
        }
    }

    // Edit form
    public function edit($id)
    {
        $products = $this->readCSVData();

        $index = $this->findIndexById($products, (string)$id);
        if ($index === null) {
            return redirect()
                ->route(self::ROUTE_LIST)
                ->with('error', 'Produit introuvable.');
        }

        return view(self::VIEW_EDIT, [
            'product' => $products[$index],
        ]);
    }

    // Update
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'category'     => ['nullable', 'string', 'max:255'],
            'price'        => ['required', 'numeric', 'min:0'],
            'currency'     => ['required', 'string', 'max:10'],
            'stock'        => ['required', 'integer', 'min:0'],
            'color'        => ['nullable', 'string', 'max:50'],
            'size'         => ['nullable', 'string', 'max:50'],
            'availability' => ['required', 'string', 'max:50'],
        ]);

        $products = $this->readCSVData();

        $index = $this->findIndexById($products, (string)$id);
        if ($index === null) {
            return redirect()
                ->route(self::ROUTE_LIST)
                ->with('error', 'Produit introuvable.');
        }

        $products[$index] = $this->normalizeProduct(array_merge($products[$index], $validated, [
            'id' => (string)$id, // on conserve l’ID
        ]));

        $this->writeCSVData($products);

        return redirect()
            ->route(self::ROUTE_LIST)
            ->with('success', 'Produit modifié avec succès.');
    }
}
