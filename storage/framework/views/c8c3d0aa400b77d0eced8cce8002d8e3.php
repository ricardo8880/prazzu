<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

         <?php $__env->slot('heading', null, []); ?> 
            Histórico de alterações
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('description', null, []); ?> 
            Visualização das mudanças realizadas neste item.
         <?php $__env->endSlot(); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activities->isEmpty()): ?>
            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-5 py-6 text-sm text-gray-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-400">
                Nenhuma alteração registrada ainda.
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $badgeClasses = match ($activity['evento']) {
                            'created' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400',
                            'updated' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400',
                            'deleted' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400',
                            'status_manual', 'status_manual_em_lote' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400',
                            'status_automatico' => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400',
                            default => 'bg-gray-100 text-gray-700 ring-gray-500/20 dark:bg-white/5 dark:text-gray-300',
                        };

                        $borderClasses = match ($activity['evento']) {
                            'created' => 'border-l-4 border-emerald-400',
                            'updated' => 'border-l-4 border-sky-400',
                            'deleted' => 'border-l-4 border-rose-400',
                            'status_manual', 'status_manual_em_lote' => 'border-l-4 border-amber-400',
                            'status_automatico' => 'border-l-4 border-orange-400',
                            default => 'border-l-4 border-gray-300 dark:border-white/10',
                        };

                        $old = collect($activity['old'] ?? []);
                        $attributes = collect($activity['attributes'] ?? []);

                        $campos = $old
                            ->pluck('campo')
                            ->merge($attributes->pluck('campo'))
                            ->unique()
                            ->values();

                        $oldMap = $old->keyBy('campo');
                        $newMap = $attributes->keyBy('campo');
                    ?>

                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900 <?php echo e($borderClasses); ?>">
                        <div class="border-b border-gray-100 px-5 py-4 dark:border-white/10">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($activity['descricao']); ?>

                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span><?php echo e($activity['usuario']); ?></span>
                                        <span>•</span>
                                        <span><?php echo e($activity['data']); ?></span>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide ring-1 ring-inset <?php echo e($badgeClasses); ?>">
                                        <?php echo e(str($activity['evento'] ?? 'evento')->replace('_', ' ')->title()); ?>

                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="overflow-hidden rounded-xl border border-gray-100 dark:border-white/10">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Campo
                                            </th>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Valor anterior
                                            </th>
                                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Novo valor
                                            </th>
                                        </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($campos->isEmpty()): ?>
                                            <tr>
                                                <td colspan="3" class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    Nenhuma diferença detalhada registrada para este evento.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $campos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $campo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <?php
                                                    $valorAnterior = $oldMap->get($campo)['valor'] ?? '-';
                                                    $valorNovo = $newMap->get($campo)['valor'] ?? '-';
                                                    $houveMudanca = $valorAnterior !== $valorNovo;
                                                ?>

                                                <tr class="<?php echo e($index % 2 === 0 ? 'bg-white dark:bg-transparent' : 'bg-gray-50/50 dark:bg-white/[0.02]'); ?>">
                                                    <td class="px-4 py-4 align-top text-sm font-medium text-gray-900 dark:text-white">
                                                        <?php echo e($campo); ?>

                                                    </td>

                                                    <td class="px-4 py-4 align-top text-sm text-gray-600 dark:text-gray-300">
                                                        <div class="max-w-lg whitespace-pre-wrap break-words rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/[0.03]">
                                                            <?php echo e($valorAnterior); ?>

                                                        </div>
                                                    </td>

                                                    <td class="px-4 py-4 align-top text-sm text-gray-700 dark:text-gray-200">
                                                        <div class="max-w-lg whitespace-pre-wrap break-words rounded-lg px-3 py-2 <?php echo e($houveMudanca
                                                                ? 'bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20'
                                                                : 'bg-gray-50 text-gray-600 dark:bg-white/[0.03] dark:text-gray-300'); ?>">
                                                            <?php echo e($valorNovo); ?>

                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\resources\item-controles\widgets\item-controle-historico-widget.blade.php ENDPATH**/ ?>