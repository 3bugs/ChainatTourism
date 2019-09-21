package th.ac.dusit.dbizcom.chainattourism;

import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Rect;
import android.net.Uri;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.design.widget.AppBarLayout;
import android.support.design.widget.CollapsingToolbarLayout;
import android.support.v4.widget.CircularProgressDrawable;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.support.v7.widget.Toolbar;
import android.text.SpannableString;
import android.text.style.LeadingMarginSpan;
import android.util.DisplayMetrics;
import android.util.Log;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import com.bumptech.glide.Glide;
import com.google.gson.Gson;

import java.util.Locale;

import retrofit2.Call;
import retrofit2.Retrofit;
import th.ac.dusit.dbizcom.chainattourism.etc.Utils;
import th.ac.dusit.dbizcom.chainattourism.model.Otop;
import th.ac.dusit.dbizcom.chainattourism.model.Place;
import th.ac.dusit.dbizcom.chainattourism.net.AddRatingResponse;
import th.ac.dusit.dbizcom.chainattourism.net.ApiClient;
import th.ac.dusit.dbizcom.chainattourism.net.MyRetrofitCallback;
import th.ac.dusit.dbizcom.chainattourism.net.WebServices;

import static th.ac.dusit.dbizcom.chainattourism.net.ApiClient.IMAGE_BASE_URL;

public class PlaceDetailsActivity extends AppCompatActivity {

    private static final String TAG = PlaceDetailsActivity.class.getName();
    static final String KEY_PLACE_JSON = "place_json";
    static final String KEY_OTOP_JSON = "otop_json";

    private Place mPlace = null;
    private Otop mOtop = null;
    private int mRate = 0;

    private View mProgressView;

    private CollapsingToolbarLayout mCollapsingToolbarLayout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_place_details);

        Intent intent = getIntent();

        String placeJson = intent.getStringExtra(KEY_PLACE_JSON);
        if (placeJson != null) {
            mPlace = new Gson().fromJson(placeJson, Place.class);
        } else {
            String otopJson = intent.getStringExtra(KEY_OTOP_JSON);
            mOtop = new Gson().fromJson(otopJson, Otop.class);
        }

        Toolbar toolbar = findViewById(R.id.toolbar);
        final TextView titleTextView = toolbar.findViewById(R.id.title_text_view);
        titleTextView.setText(mPlace != null ? mPlace.name : mOtop.name);
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
        setupRating();
    }

    private void populateUi() {
        setRate(mPlace != null ? mPlace.averageRate : mOtop.averageRate);

        CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(this);
        circularProgressDrawable.setStrokeWidth(5f);
        circularProgressDrawable.setCenterRadius(30f);
        circularProgressDrawable.start();

        ImageView coverImageView = findViewById(R.id.cover_image_view);
        Glide.with(this)
                .load(IMAGE_BASE_URL + (mPlace != null ? mPlace.coverImage : mOtop.coverImage))
                .placeholder(circularProgressDrawable)
                .into(coverImageView);

        TextView placeNameTextView = findViewById(R.id.place_name_text_view);
        placeNameTextView.setText(mPlace != null ? mPlace.name : mOtop.name);

        TextView phoneTextView = findViewById(R.id.phone_text_view);
        TextView openingTimeTextView = findViewById(R.id.opening_time_text_view);
        TextView addressTextView = findViewById(R.id.address_text_view);
        phoneTextView.setText(mPlace != null ? mPlace.phone : mOtop.phone);
        openingTimeTextView.setText(mPlace != null ? mPlace.openingTime : mOtop.openingTime);
        addressTextView.setText(mPlace != null ? mPlace.address : mOtop.address);

        TextView detailsTextView = findViewById(R.id.details_text_view);
        detailsTextView.setText(createIndentedText(mPlace != null ? mPlace.details : mOtop.details, 100, 0));

        Button otopContactButton = findViewById(R.id.otop_contact_button);
        otopContactButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent i = new Intent(Intent.ACTION_VIEW);
                i.setData(Uri.parse(mOtop.contactUrl));
                startActivity(i);
            }
        });

        if (mPlace != null) {
            otopContactButton.setVisibility(View.GONE);
            setupGalleryImages();
        } else if (mOtop != null) {
            otopContactButton.setVisibility(View.VISIBLE);
            setupGalleryImagesOtop();
        }
    }

    private void setRate(float averageRate) {
        TextView rateTextView = findViewById(R.id.rate_text_view);
        if (averageRate != 0) {
            rateTextView.setText(String.valueOf(averageRate));
        } else {
            rateTextView.setText(String.valueOf("ไม่มีข้อมูล"));
        }
    }

    private void setupGalleryImages() {
        LinearLayoutManager layoutManager
                = new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false);
        GalleryImagesAdapter adapter = new GalleryImagesAdapter(
                this,
                mPlace
        );

        RecyclerView galleryImagesRecyclerView = findViewById(R.id.gallery_images_recycler_view);
        galleryImagesRecyclerView.setLayoutManager(layoutManager);
        galleryImagesRecyclerView.addItemDecoration(new SpacingDecoration(this));
        galleryImagesRecyclerView.setAdapter(adapter);
    }

    private void setupGalleryImagesOtop() {
        LinearLayoutManager layoutManager
                = new LinearLayoutManager(this, LinearLayoutManager.HORIZONTAL, false);
        GalleryImagesOtopAdapter adapter = new GalleryImagesOtopAdapter(
                this,
                mOtop
        );

        RecyclerView galleryImagesRecyclerView = findViewById(R.id.gallery_images_recycler_view);
        galleryImagesRecyclerView.setLayoutManager(layoutManager);
        galleryImagesRecyclerView.addItemDecoration(new SpacingDecoration(this));
        galleryImagesRecyclerView.setAdapter(adapter);
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
                                mPlace != null ? mPlace.latitude : mOtop.latitude,
                                mPlace != null ? mPlace.longitude : mOtop.longitude
                        )
                );
                Intent intent = new Intent(PlaceDetailsActivity.this, MapsActivity.class);
                if (mPlace != null) {
                    intent.putExtra(KEY_PLACE_JSON, new Gson().toJson(mPlace));
                } else {
                    intent.putExtra(KEY_OTOP_JSON, new Gson().toJson(mOtop));
                }
                startActivity(intent);
            }
        });
    }

    private void setupRating() {
        mProgressView = findViewById(R.id.progress_view);

        ImageView star1ImageView = findViewById(R.id.star_1_image_view);
        ImageView star2ImageView = findViewById(R.id.star_2_image_view);
        ImageView star3ImageView = findViewById(R.id.star_3_image_view);
        ImageView star4ImageView = findViewById(R.id.star_4_image_view);
        ImageView star5ImageView = findViewById(R.id.star_5_image_view);

        final ImageView[] starImageViewList = new ImageView[]{
                star1ImageView, star2ImageView, star3ImageView, star4ImageView, star5ImageView
        };

        View.OnClickListener starsListener = new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                ImageView imageView = (ImageView) v;

                int i = 0;
                for (; i < starImageViewList.length; i++) {
                    starImageViewList[i].setImageResource(R.drawable.ic_star_on);
                    if (starImageViewList[i] == imageView) {
                        i++;
                        break;
                    }
                }
                mRate = i;
                for (int j = i; j < starImageViewList.length; j++) {
                    starImageViewList[j].setImageResource(R.drawable.ic_star_off);
                }
            }
        };

        star1ImageView.setOnClickListener(starsListener);
        star2ImageView.setOnClickListener(starsListener);
        star3ImageView.setOnClickListener(starsListener);
        star4ImageView.setOnClickListener(starsListener);
        star5ImageView.setOnClickListener(starsListener);

        findViewById(R.id.submit_rating_button).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mRate > 0) {
                    //Toast.makeText(PlaceDetailsActivity.this, mRate + " ดาว", Toast.LENGTH_LONG).show();
                    new AlertDialog.Builder(PlaceDetailsActivity.this)
                            .setTitle("ให้คะแนนความพึงพอใจ")
                            .setMessage("ยืนยันให้คะแนนความพึงพอใจ " + mRate + " ดาว ?")
                            .setPositiveButton("ให้คะแนน " + mRate + " ดาว", new DialogInterface.OnClickListener() {
                                @Override
                                public void onClick(DialogInterface dialog, int which) {
                                    doAddRating();
                                }
                            })
                            .setNegativeButton("ยกเลิก", null)
                            .show();
                } else {
                    Utils.showShortToast(PlaceDetailsActivity.this, "กรุณากดดาวเพื่อระบุคะแนนที่จะให้");
                }
            }
        });
    }

    private void doAddRating() {
        mProgressView.setVisibility(View.VISIBLE);

        Retrofit retrofit = ApiClient.getClient();
        WebServices services = retrofit.create(WebServices.class);

        int id = mPlace != null ? mPlace.id : mOtop.id;
        String type = mPlace != null ? "place" : "otop";

        Call<AddRatingResponse> call = services.addRating(id, type, mRate);
        call.enqueue(new MyRetrofitCallback<>(
                PlaceDetailsActivity.this,
                null,
                mProgressView,
                new MyRetrofitCallback.MyRetrofitCallbackListener<AddRatingResponse>() {
                    @Override
                    public void onSuccess(AddRatingResponse responseBody) {
                        float averageRate = responseBody.averageRate;
                        setRate(averageRate);

                        if (mPlace != null) {
                            mPlace.setAverageRate(averageRate);
                        } else if (mOtop != null) {
                            mOtop.setAverageRate(averageRate);
                        }

                        Utils.showShortToast(PlaceDetailsActivity.this, responseBody.errorMessage);
                    }

                    @Override
                    public void onError(String errorMessage) {
                        Utils.showOkDialog(PlaceDetailsActivity.this, "Error", errorMessage, null);
                    }
                }
        ));
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

    private static class GalleryImagesAdapter extends RecyclerView.Adapter<GalleryImagesAdapter.ViewHolder> {

        private final Context mContext;
        private final Place mPlace;

        GalleryImagesAdapter(Context context, Place place) {
            mContext = context;
            mPlace = place;
        }

        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    R.layout.item_gallery_image, parent, false
            );
            return new ViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            final String imageFileName = mPlace.galleryImages.get(position);
            holder.mImageFileName = imageFileName;

            CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(mContext);
            circularProgressDrawable.setStrokeWidth(5f);
            circularProgressDrawable.setCenterRadius(30f);
            circularProgressDrawable.start();

            Log.i(TAG, ApiClient.GALLERY_BASE_URL.concat(imageFileName));

            Glide.with(mContext)
                    .load(ApiClient.GALLERY_BASE_URL.concat(imageFileName))
                    .placeholder(circularProgressDrawable)
                    .into(holder.mImageView);
        }

        @Override
        public int getItemCount() {
            return mPlace.galleryImages.size();
        }

        class ViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final ImageView mImageView;

            private String mImageFileName;

            ViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mImageView = itemView.findViewById(R.id.image_view);

                mImageView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        //Utils.showShortToast(mContext, mImageFileName);
                        Intent intent = new Intent(mContext, GalleryActivity.class);
                        intent.putExtra("current_index", getAdapterPosition());
                        intent.putExtra("place_json", new Gson().toJson(mPlace));
                        mContext.startActivity(intent);
                    }
                });
            }
        }
    }

    private static class GalleryImagesOtopAdapter extends RecyclerView.Adapter<GalleryImagesOtopAdapter.ViewHolder> {

        private final Context mContext;
        private final Otop mOtop;

        GalleryImagesOtopAdapter(Context context, Otop otop) {
            mContext = context;
            mOtop = otop;
        }

        @NonNull
        @Override
        public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            View view = LayoutInflater.from(parent.getContext()).inflate(
                    R.layout.item_gallery_image, parent, false
            );
            return new ViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
            final String imageFileName = mOtop.galleryImages.get(position);
            holder.mImageFileName = imageFileName;

            CircularProgressDrawable circularProgressDrawable = new CircularProgressDrawable(mContext);
            circularProgressDrawable.setStrokeWidth(5f);
            circularProgressDrawable.setCenterRadius(30f);
            circularProgressDrawable.start();

            Log.i(TAG, ApiClient.GALLERY_BASE_URL.concat(imageFileName));

            Glide.with(mContext)
                    .load(ApiClient.GALLERY_BASE_URL.concat(imageFileName))
                    .placeholder(circularProgressDrawable)
                    .into(holder.mImageView);
        }

        @Override
        public int getItemCount() {
            return mOtop.galleryImages.size();
        }

        class ViewHolder extends RecyclerView.ViewHolder {

            private final View mRootView;
            private final ImageView mImageView;

            private String mImageFileName;

            ViewHolder(View itemView) {
                super(itemView);

                mRootView = itemView;
                mImageView = itemView.findViewById(R.id.image_view);

                mImageView.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        //Utils.showShortToast(mContext, mImageFileName);
                        Intent intent = new Intent(mContext, GalleryActivity.class);
                        intent.putExtra("current_index", getAdapterPosition());
                        intent.putExtra("place_json", new Gson().toJson(mOtop));
                        mContext.startActivity(intent);
                    }
                });
            }
        }
    }

    public class SpacingDecoration extends RecyclerView.ItemDecoration {

        private final static int MARGIN_LEFT_IN_DP = 0;
        private final static int MARGIN_RIGHT_IN_DP = 16;
        private final int mMarginLeft, mMarginRight;

        SpacingDecoration(@NonNull Context context) {
            DisplayMetrics metrics = context.getResources().getDisplayMetrics();
            mMarginLeft = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_LEFT_IN_DP,
                    metrics
            );
            mMarginRight = (int) TypedValue.applyDimension(
                    TypedValue.COMPLEX_UNIT_DIP,
                    MARGIN_RIGHT_IN_DP,
                    metrics
            );
        }

        @Override
        public void getItemOffsets(@NonNull Rect outRect, @NonNull View view,
                                   @NonNull RecyclerView parent,
                                   @NonNull RecyclerView.State state) {
            final int itemPosition = parent.getChildAdapterPosition(view);
            if (itemPosition == RecyclerView.NO_POSITION) {
                return;
            }
            if (itemPosition == 0) {
                outRect.left = mMarginLeft;
            }
            final RecyclerView.Adapter adapter = parent.getAdapter();
            if ((adapter != null) && (itemPosition == adapter.getItemCount() - 1)) {
                outRect.right = mMarginRight;
            }
        }
    }

}
