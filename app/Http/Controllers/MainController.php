<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function PHPUnit\Framework\isNumeric;

class MainController extends Controller
{
    function readCSVData()
    {
        try {
            $fileContent = [];
            $filePath = storage_path('app/data/products-data.csv');
            if (file_exists($filePath)) {
                $myFile = fopen($filePath, "r");
                $i = 0;
                while (($row = fgetcsv($myFile, 1000, ",")) !== FALSE) {
                    if ($i == 0) {
                        $i++;
                        continue;
                    }
                    array_push($fileContent, [
                        "id" => $row[0],
                        "name" => $row[1],
                        "description" => $row[2],
                        "category" => $row[3],
                        "price" => $row[4],
                        "currency" => $row[5],
                        "stock" => $row[6],
                        "color" => $row[7],
                        "size" => $row[8],
                        "availability" => $row[9],
                    ]);
                    $i++;
                }
                fclose($myFile);
            }
        } catch (\Exception $e) {
            dd("An error occurred while reading the CSV file: " . $e->getMessage());
        }
        return $fileContent;
    }

    function writeCSVData($productData)
    {
        $filePath = storage_path('app/data/products-data.csv');
        if (file_exists($filePath)) {
            $myFile = fopen($filePath, "w");
            fputcsv($myFile, ["id", "name", "description", "category", "price", "currency", "stock", "color", "size", "availability"], ',', '"', '');
            foreach ($productData as $line) {
                fputcsv($myFile, $line, ',', '"', '');
            }
            fclose($myFile);
        }
    }

    //Créer une representation du produit
    // function createRepOfProduct($fileContent)
    // {
    //     $output = [];
    //     foreach ($fileContent as $value) {
    //         $output[] = [
    //             "id" => $value['id'],
    //             "name" => $value['name'],
    //             "price" => $value['price'],
    //             "stock" => $value['stock'],
    //             "availability" => $value['availability'],
    //         ];
    //     }
    //     return $output;
    // }

    // afficher la page d'accueil
    function index()
    {
        return view('home');
    }

    // afficher la liste des produits
    function show()
    {
        // $products = $this->createRepOfProduct($this->readCSVData());
        $products = $this->readCSVData();
        // dd($products);
        return view('Productlist', ["products" => $products]);
    }

    function delete($id)
    {
        try {
            $products = $this->readCSVData();
            $newProducts = array_filter($products, function ($product) use ($id) {
                return $product['id'] != $id;
            });
            $this->writeCSVData($newProducts);
            return redirect()->route('afficher-liste')->with('success', 'Product deleted successfully.');
            // le code ci-après  va passer mais ce n'est pas la bonne façon de faire
            // return view('Productlist', ["products" => $newProducts])->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('afficher-liste')->with('error', 'An error occurred while deleting the product: ' . $e->getMessage());
            // le code ci-après  va passer mais ce n'est pas la bonne façon de faire
            // return view('Productlist', ["products" => $newProducts])->with('error', 'An error occurred while deleting the product: ' . $e->getMessage());
        }

    }

    function edit($id)
    {
       // À implémenter : afficher un formulaire pour éditer le produit avec l'ID donné
       /*
            1. Lire les données du CSV
            2. Trouver le produit avec l'ID donné
            3. Passer les données du produit à une vue d'édition
            4. La vue d'édition affichera un formulaire pré-rempli avec les données
       */
    }


    // function indexFrWithDay($day)
    // {
    //     try {
    //         $dayMapping = [
    //             "1" => "Lundi",
    //             "2" => "Mardi",
    //             "3" => "Mercredi",
    //             "4" => "Jeudi",
    //             "5" => "Vendredi",
    //             "6" => "Samedi",
    //             "7" => "Dimanche"
    //         ];
    //         $textToDisplay = "Bonjour, aujourd'hui c'est ";
    //         if (!in_array("" . $day, ["1", "2", "3", "4", "5", "6", "7"])) {
    //             throw new \Exception("Le jour doit être entre 1 et 7 ou un nom de jour valide.");
    //         } elseif (isNumeric($day)) {
    //             $textToDisplay .= $dayMapping[$day];
    //         } elseif (gettype($day) === "string" && in_array(ucfirst(strtolower($day)), $dayMapping)) {
    //             $textToDisplay .= ucfirst(strtolower($day));
    //         }
    //     } catch (\Exception $e) {
    //         $textToDisplay = "Paramètre de jour invalide : " . $e->getMessage();
    //     }

    //     return view('home', ["textToDisplay" => $textToDisplay]);
    // }



    //  function indexEnWithDay($day)
    // {
    //     try {
    //         $dayMapping = [
    //             "1" => "Monday",
    //             "2" => "Tuesday",
    //             "3" => "Wednesday",
    //             "4" => "Thursday",
    //             "5" => "Friday",
    //             "6" => "Saturday",
    //             "7" => "Sunday"
    //         ];

    //         $textToDisplay = "Hello, today is ";
    //         if (!in_array("" . $day, ["1", "2", "3", "4", "5", "6", "7"])) {
    //             throw new \Exception("Day must be between 1 and 7 or a valid day name.");
    //         } elseif (isNumeric($day)) {
    //             $textToDisplay .= $dayMapping[$day];
    //         } elseif (gettype($day) === "string" && in_array(ucfirst(strtolower($day)), $dayMapping)) {
    //             $textToDisplay .= ucfirst(strtolower($day));
    //         }
    //     } catch (\Exception $e) {
    //         $textToDisplay = "Invalid day parameter: " . $e->getMessage();
    //     }

    //     return view('home', ["textToDisplay" => $textToDisplay]);
    // }


}
