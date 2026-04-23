<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class searchPokeName extends Controller
{
   // Ganti semua return di controller menjadi selalu JSON
// Hapus semua : view('cards.search', ...)

public function search(Request $request){$name=trim($request->input('name',''));$type=trim($request->input('type',''));$rarity=trim($request->input('rarity',''));$page=$request->input('page',1);$perPage=20;$qParts=[];if($name)$qParts[]='name:'.$name.'*';if($type)$qParts[]='types:'.$type;if($rarity)$qParts[]='rarity:"'.$rarity.'"';$query=empty($qParts)?'*':implode(' ',$qParts);try{$response=Http::timeout(10)->get('https://api.pokemontcg.io/v2/cards',['q'=>$query,'page'=>$page,'pageSize'=>$perPage]);if(!$response->successful())throw new \Exception('API Error');$json=$response->json();$total=$json['totalCount']??0;$cards=$json['data']??[];$data=array_map(fn($card)=>['id'=>$card['id'],'name'=>$card['name'],'image'=>$card['images']['small']??null,'type'=>$card['types'][0]??'','rarity'=>$card['rarity']??''],$cards);return response()->json(['data'=>$data,'page'=>$page,'perPage'=>$perPage,'total'=>$total]);}catch(\Throwable $e){return response()->json(['data'=>[],'page'=>$page,'perPage'=>$perPage,'total'=>0,'error'=>$e->getMessage()],500);}}
}