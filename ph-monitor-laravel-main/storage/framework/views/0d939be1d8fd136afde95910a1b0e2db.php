<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mediciones de pH</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Reporte de Mediciones de pH</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>pH</th>
                <th>Superficie</th>
                <th>Fecha</th>
                <th>Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $mediciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($m->valor_ph); ?></td>
                    <td><?php echo e($m->tipo_superficie); ?></td>
                    <td><?php echo e($m->fecha); ?></td>
                    <td><?php echo e($m->hora); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\Users\CESAR\Downloads\ph-monitor-laravel-main\ph-monitor-laravel-main\resources\views/mediciones/reporte.blade.php ENDPATH**/ ?>