<?php print $DSP->form_open(
				array('action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings'),
				array('name' => strtolower(LG_AU_extension_class))
);?>

<div class="tg">
	<h2><?php print $LANG->line("check_for_updates_title") ?></h2>
	<div class="info"><?php print str_replace("{addon_name}", $this->name, $LANG->line("check_for_updates_info")); ?></div>
	<table>
		<tbody>
			<tr class="odd">
				<th style="width:400px;"><?php print  $LANG->line("check_for_updates_label") ?></th>
				<td>
					<select name="check_for_updates">
						<option value="y">
							<?php print $LANG->line("yes") ?>
						</option>
						<option value="n">
							<?php print $LANG->line("no") ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th style="width:400px;"><?php print  $LANG->line("cache_refresh_label") ?></th>
				<td><input type="text" value="<?php print $REGX->form_prep($settings['cache_refresh']); ?>" /></td>
			</tr>
		</tbody>
	</table>
</div>

<div class="tg">
	<h2><?php print $LANG->line("check_for_extension_updates_title") ?></h2>
	<div class="info"><?php print str_replace("{addon_name}", $this->name, $LANG->line("check_for_extension_updates_info")); ?></div>
	<table>
		<tbody>
			<tr class="odd">
				<th style="width:400px;"><?php print $LANG->line("check_for_extension_updates_label") ?></th>
				<td>
					<select<?php if(!$lgau_enabled) : ?> disabled="disabled"<?php endif; ?> name="check_for_extension_updates">
						<option value="y"<?php print ($settings["check_for_extension_updates"] == "y" && $lgau_enabled === TRUE) ? 'selected="selected"' : ''; ?>>
							<?php print $LANG->line("yes") ?>
						</option>
						<option value="n"<?php print ($settings["check_for_extension_updates"] == "n" || $lgau_enabled === FALSE) ? 'selected="selected"' : ''; ?>>
							<?php print $LANG->line("no") ?>
						</option>
					</select>
					<?php if(!$lgau_enabled) : ?>
						&nbsp;
						<span class='highlight'>LG Addon Updater is not installed and activated.</span>
						<input type="hidden" name="check_for_extension_updates" value="0" />
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<input type="submit" value="<?php print $LANG->line('save_extension_settings') ?>" />