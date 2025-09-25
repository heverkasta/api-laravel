<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\Alimento;


Route::get('/alimento', function () {
    return Alimento::all();
});//foi


Route::get('/alimento/{id}', function ($id) {
    return Alimento::findOrFail($id);
});//foi


Route::post('/alimento', function(Request $request) {
    $data = $request->validate([
        'nome'           => ['required', 'string', 'max:50'],
        'quantidade'     => ['required', 'integer', 'min:0'],
        'unidade_medida' => ['required', 'string', 'max:50']
    ]);

    $alimento = new Alimento();
    $alimento->nome = $data['nome'];
    $alimento->quantidade = $data['quantidade'];
    $alimento->unidade_medida = $data['unidade_medida'];
    $alimento->save(); 

    return response()->json($alimento, 201);
});//foi


Route::put('/alimento/{id}', function(Request $request, $id) {
    $alimento = Alimento::findOrFail($id);

    $data = $request->validate([
        'nome'           => ['required', 'string', 'max:50'],
        'quantidade'     => ['required', 'integer', 'min:0'],
        'unidade_medida' => ['required', 'string', 'max:50']
    ]);

    $alimento->nome = $data['nome'];
    $alimento->quantidade = $data['quantidade'];
    $alimento->unidade_medida = $data['unidade_medida'];
    $alimento->save(); 

    return response()->json($alimento);
});//foi


Route::delete('/alimento/{id}', function($id) {
    $alimento = Alimento::findOrFail($id);
    $alimento->delete();

    return response()->noContent();
});//foi


Route::get('/receita/gerar', function() {
    $api_token = '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';
    $lista = "";
    foreach(Alimento::all() as $linha){
        $lista .= $linha['quantidade']." ".$linha['unidade_medida']." de ".$linha['nome'].", ";
    }
    $prompt = "Faça uma receita extremamente deliciosa usando APENAS ".$lista."mas não precisa usar tudo e se necessario, pode usar ingredientes basicos que toda casa tem como agua, sal e açucar. Por favor, formate em markdown.";

    $response = Http::timeout(30)->withHeaders([
        'Authorization' => 'Bearer ' . $api_token,
        'Content-Type' => 'application/json',
    ])->post('https://router.huggingface.co/v1/chat/completions', [
        'model' => 'deepseek-ai/DeepSeek-V3.1-Terminus',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 1000,
        'temperature' => 0.7,
        'stream' => false
    ]);
    
    if ($response->successful()) {
        $result = $response->json();
        $receita = $result['choices'][0]['message']['content'] ?? 'Receita não disponivel';
        return response()->json($receita);
    }

    return response()->json(['msgErr' => 'Algo deu errado'], 500);
});