package th.ac.dusit.dbizcom.chainattourism;

import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.AppBarLayout;
import android.support.design.widget.CollapsingToolbarLayout;
import android.support.v4.widget.CircularProgressDrawable;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.text.SpannableString;
import android.text.style.LeadingMarginSpan;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.google.gson.Gson;

import java.util.Locale;

import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.model.Place;

import static th.ac.dusit.dbizcom.chainattourism.net.ApiClient.IMAGE_BASE_URL;

public class PlaceDetailsActivity extends AppCompatActivity {

    private static final String TAG = PlaceDetailsActivity.class.getName();
    static final String KEY_PLACE_JSON = "place_json";

    private Place mPlace;

    private CollapsingToolbarLayout mCollapsingToolbarLayout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_place_details);

        Intent intent = getIntent();
        mPlace = new Gson().fromJson(intent.getStringExtra(KEY_PLACE_JSON), Place.class);

        Toolbar toolbar = findViewById(R.id.toolbar);
        final TextView titleTextView = toolbar.findViewById(R.id.title_text_view);
        titleTextView.setText(mPlace.name);
        titleTextView.setTextColor(getResources().getColor(android.R.color.transparent));
        setSupportActionBar(toolbar);
        //getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        /*mCollapsingToolbarLayout = findViewById(R.id.collapsing_toolbar);
        mCollapsingToolbarLayout.setTitle(itemTitle);
        mCollapsingToolbarLayout.setExpandedTitleColor(getResources().getColor(android.R.color.transparent));*/

        AppBarLayout appBarLayout = findViewById(R.id.app_bar_layout);
        appBarLayout.addOnOffsetChangedListener(new AppBarLayout.OnOffsetChangedListener() {
            @Override
            public void onOffsetChanged(AppBarLayout appBarLayout, int verticalOffset) {
                int diff = Math.abs(Math.abs(verticalOffset) - appBarLayout.getTotalScrollRange());
                Log.i(TAG, "Diff: " + diff);

                if (diff < 50) {
                    // Toolbar collapsed
                    titleTextView.setTextColor(getResources().getColor(android.R.color.white));
                } else {
                    // Toolbar expanded
                    titleTextView.setTextColor(getResources().getColor(android.R.color.transparent));
                }
            }
        });

        populateUi();
        setupToolbarIcons();
    }

    private void populateUi() {
        CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(this);
        circularProgressDrawable.setStrokeWidth(5f);
        circularProgressDrawable.setCenterRadius(30f);
        circularProgressDrawable.start();

        ImageView coverImageView = findViewById(R.id.cover_image_view);
        Glide.with(this)
                .load(IMAGE_BASE_URL + mPlace.coverImage)
                .placeholder(circularProgressDrawable)
                .into(coverImageView);

        TextView placeNameTextView = findViewById(R.id.place_name_text_view);
        placeNameTextView.setText(mPlace.name);

        TextView phoneTextView = findViewById(R.id.phone_text_view);
        TextView openingTimeTextView = findViewById(R.id.opening_time_text_view);
        TextView addressTextView = findViewById(R.id.address_text_view);
        phoneTextView.setText(mPlace.phone);
        openingTimeTextView.setText(mPlace.openingTime);
        addressTextView.setText(mPlace.address);

        TextView detailsTextView = findViewById(R.id.details_text_view);
        detailsTextView.setText(createIndentedText(mPlace.details, 100, 0));
    }

    private void setupToolbarIcons() {
        /*ปุ่มย้อนกลับ*/
        findViewById(R.id.back_image_view).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });

        /*ปุ่มแสดงหน้าแผนที่*/
        findViewById(R.id.marker_image_view).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Utils.showShortToast(
                        PlaceDetailsActivity.this,
                        String.format(
                                Locale.getDefault(),
                                "Latitude: %f\nLongitude: %f",
                                mPlace.latitude, mPlace.longitude
                        )
                );
                Intent intent = new Intent(PlaceDetailsActivity.this, MapsActivity.class);
                intent.putExtra(KEY_PLACE_JSON, new Gson().toJson(mPlace));
                startActivity(intent);
            }
        });
    }

    static SpannableString createIndentedText(String text, int marginFirstLine, int marginNextLines) {
        SpannableString result = new SpannableString(text);
        result.setSpan(
                new LeadingMarginSpan.Standard(marginFirstLine, marginNextLines),
                0,
                text.length(),
                0
        );
        return result;
    }
}
