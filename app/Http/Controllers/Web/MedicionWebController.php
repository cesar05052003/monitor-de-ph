<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Medicion;
use DateTime;
use Illuminate\Support\Facades\Storage;

class MedicionWebController extends Controller
{
    public function descargarPDF()
    {
        $mediciones = Medicion::orderBy('fecha', 'desc')->orderBy('hora', 'desc')->get();
        // Generar CSV y guardarlo temporalmente en storage/public
        $filename = 'reporte_mediciones_ph_' . date('Y-m-d_H-i-s') . '.csv';

        $csv = "Reporte de Mediciones de pH\n";
        $csv .= "Generado el: " . now()->format('d/m/Y H:i:s') . "\n\n";
        $csv .= "#,pH,Superficie,Fecha,Hora\n";

        foreach ($mediciones as $i => $medicion) {
            $csv .= ($i + 1) . ',"' . $medicion->valor_ph . '","' . $medicion->tipo_superficie . '","' . $medicion->fecha . '","' . $medicion->hora . "" . "\n";
        }

        $storagePath = 'public/' . $filename;
            try {
                // Asegurar que el directorio exista
                if (!Storage::exists('public')) {
                    Storage::makeDirectory('public');
                }

                Storage::put($storagePath, $csv);

                $fullPath = storage_path('app/' . $storagePath);

                $headers = [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'X-Content-Type-Options' => 'nosniff',
                    'Cache-Control' => 'private, max-age=0, no-cache'
                ];

                // Devolver como descarga con headers y eliminar archivo después de enviado
                return response()->download($fullPath, $filename, $headers)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                // Registrar el error para diagnóstico
                Log::error('Error en descargarPDF: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);

                // Fallback: devolver CSV en memoria (no guardado), así el usuario aún puede descargar
                $headers = [
                    'Content-Type' => 'text/csv; charset=utf-8',
                    'Content-Disposition' => 'attachment; filename="reporte_mediciones_fallback.csv"',
                    'X-Content-Type-Options' => 'nosniff'
                ];

                return response()->make($csv, 200, $headers);
            }

        $fullPath = storage_path('app/' . $storagePath);

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-cache'
        ];

        // Devolver como descarga con headers y eliminar archivo después de enviado
        return response()->download($fullPath, $filename, $headers)->deleteFileAfterSend(true);
    }

    public function index(Request $request)
    {
        $this->actualizarDesdeThingSpeak();

        $query = DB::table('mediciones');

        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->input('fecha'));
        }

        $mediciones = $query->orderByDesc('fecha')
                            ->orderByDesc('hora')
                            ->get();

        $recepcionActiva = DB::table('configuraciones')
                            ->where('clave', 'recepcion_activa')
                            ->value('valor');

        return view('mediciones.index', [
            'mediciones' => $mediciones,
            'recepcionActiva' => $recepcionActiva
        ]);
    }

    public function actualizarDesdeThingSpeak()
{
    $activo = DB::table('configuraciones')
              ->where('clave', 'recepcion_activa')
              ->value('valor');

    if (!$activo) {
        return false;
    }

    $apiKey = 'N6CLG1BHFP4YBY1R';
    $channelId = '2983047';

    $response = Http::get("https://api.thingspeak.com/channels/{$channelId}/feeds.json", [
        'api_key' => $apiKey,
        'results' => 1
    ]);

    if ($response->successful()) {
        $data = $response->json();
        $feed = $data['feeds'][0] ?? null;

        if ($feed && !empty($feed['field1'])) {
            $ph = floatval($feed['field1']);
            $fecha = date('Y-m-d', strtotime($feed['created_at']));
            $hora = date('H:i:s', strtotime($feed['created_at']));
            $entryId = $feed['entry_id'] ?? null;

            // 👇 Verifica si este entry_id ya fue registrado
            $existe = DB::table('mediciones')
                      ->where('entry_id', $entryId)
                      ->exists();

            if (!$existe) {
                DB::table('mediciones')->insert([
                    'valor_ph' => $ph,
                    'tipo_superficie' => 'Importado',
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'entry_id' => $entryId // 👈 Guardamos el ID único de la medición
                ]);

                return true;
            }
        }
    }

    return false;
}

    public function getLatestMeasurement()
    {
        $this->actualizarDesdeThingSpeak();

        $latest = DB::table('mediciones')
                   ->orderByDesc('fecha')
                   ->orderByDesc('hora')
                   ->first();

        return response()->json($latest);
    }

    public function destroy($id)
    {
        try {
            $medicion = DB::table('mediciones')->where('id', $id)->first();

            if (!$medicion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Medición no encontrada'
                ], 404);
            }

            DB::table('mediciones')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Medición eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la medición',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleRecepcion()
    {
        $estadoActual = DB::table('configuraciones')
                        ->where('clave', 'recepcion_activa')
                        ->value('valor');

        DB::table('configuraciones')
            ->where('clave', 'recepcion_activa')
            ->update(['valor' => !$estadoActual]);

        return redirect()->route('mediciones.index');
    }
}
